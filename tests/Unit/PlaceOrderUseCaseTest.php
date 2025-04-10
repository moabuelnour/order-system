<?php

namespace Tests\Unit;

use App\Domain\Entities\Ingredient;
use App\Domain\Entities\Order;
use App\Domain\Entities\Product;
use App\Domain\UseCases\Order\PlaceOrderUseCase;
use App\Interfaces\IngredientRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\StockNotifierInterface;
use Mockery;
use Tests\TestCase;

class PlaceOrderUseCaseTest extends TestCase
{
    public function test_place_order_updates_stock_and_notifies()
    {
        $orderRepo = Mockery::mock(OrderRepositoryInterface::class);
        $ingredientRepo = Mockery::mock(IngredientRepositoryInterface::class);
        $notifier = Mockery::mock(StockNotifierInterface::class);

        $onion = new Ingredient('Onion', 510, 1000);
        $onion->setId(1);
        $product = new Product(1, 'Burger', [[$onion, 20]]);

        $orderRepo->shouldReceive('save')->once()->andReturnUsing(function ($order) {
            $order->setId(1);
        });
        $ingredientRepo->shouldReceive('getProductWithIngredients')->with(1)->andReturn($product);
        $ingredientRepo->shouldReceive('update')->once();
        $notifier->shouldReceive('notify')->once();

        $useCase = new PlaceOrderUseCase($orderRepo, $ingredientRepo, $notifier);
        $order = $useCase->execute([['product_id' => 1, 'quantity' => 1]]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(1, $order->getId());
        $this->assertEquals(490, $onion->getStock());
    }

    public function test_insufficient_stock_throws_exception()
    {
        $orderRepo = Mockery::mock(OrderRepositoryInterface::class);
        $ingredientRepo = Mockery::mock(IngredientRepositoryInterface::class);
        $notifier = Mockery::mock(StockNotifierInterface::class);

        $beef = new Ingredient('Beef', 100, 20000);
        $beef->setId(1);
        $product = new Product(1, 'Burger', [[$beef, 150]]);

        $orderRepo->shouldReceive('save')->once()->andReturnUsing(function ($order) {
            $order->setId(1); // Allow save to be called in transaction
        });
        $ingredientRepo->shouldReceive('getProductWithIngredients')->with(1)->andReturn($product);
        // No update expected since it should fail before

        $useCase = new PlaceOrderUseCase($orderRepo, $ingredientRepo, $notifier);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Insufficient stock for Beef');
        $useCase->execute([['product_id' => 1, 'quantity' => 1]]);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
