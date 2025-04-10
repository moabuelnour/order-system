<?php

namespace App\Interfaces;

use App\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;
}
