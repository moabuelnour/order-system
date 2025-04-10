<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Order;
use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order as EloquentOrder;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function save(Order $order): void
    {
        $eloquentOrder = new EloquentOrder();
        $eloquentOrder->save();

        $pivotData = [];
        foreach ($order->getProducts() as [$product, $quantity]) {
            $pivotData[$product->getId()] = ['quantity' => $quantity];
        }
        $eloquentOrder->products()->sync($pivotData);

        $order->setId($eloquentOrder->id);
    }
}
