<?php

namespace App\Interfaces;

use App\Domain\Entities\Ingredient;

interface StockNotifierInterface
{
    public function notify(Ingredient $ingredient): void;
}
