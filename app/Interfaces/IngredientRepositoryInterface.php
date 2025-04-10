<?php

namespace App\Interfaces;

use App\Domain\Entities\Ingredient;
use App\Domain\Entities\Product;

interface IngredientRepositoryInterface
{
    public function findByName(string $name): Ingredient;

    public function update(Ingredient $ingredient): void;

    public function getProductWithIngredients(int $productId): Product;
}
