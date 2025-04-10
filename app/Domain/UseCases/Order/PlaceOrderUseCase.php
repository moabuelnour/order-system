<?php

namespace App\Domain\UseCases\Order;

use App\Domain\Entities\Order;
use App\Interfaces\IngredientRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\StockNotifierInterface;
use Illuminate\Support\Facades\DB;

class PlaceOrderUseCase
{
    private $orderRepository;

    private $ingredientRepository;

    private $stockNotifier;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        IngredientRepositoryInterface $ingredientRepository,
        StockNotifierInterface $stockNotifier
    ) {
        $this->orderRepository = $orderRepository;
        $this->ingredientRepository = $ingredientRepository;
        $this->stockNotifier = $stockNotifier;
    }

    public function execute(array $productsData): Order
    {
        $products = $this->mapProducts($productsData);
        $order = new Order($products);
        DB::beginTransaction();
        try {
            $this->orderRepository->save($order);
            $this->updateStock($products);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // Re-throw to handle in controller (e.g., return error response)
        }

        return $order;
    }

    private function mapProducts(array $productsData): array
    {
        $products = [];
        foreach ($productsData as $item) {
            $product = $this->ingredientRepository->getProductWithIngredients($item['product_id']);
            $products[] = [$product, $item['quantity']];
        }

        return $products;
    }

    private function updateStock(array $products): void
    {
        foreach ($products as [$product, $quantity]) {
            foreach ($product->getIngredients() as [$ingredient, $amount]) {
                $ingredient->reduceStock($amount * $quantity);
                if ($ingredient->isBelowThreshold() && ! $ingredient->hasAlertSent()) {
                    $this->stockNotifier->notify($ingredient);
                    $ingredient->markAlertSent();
                }
                $this->ingredientRepository->update($ingredient);
            }
        }
    }
}
