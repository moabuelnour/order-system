<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Ingredient;
use App\Domain\Entities\Product;
use App\Interfaces\IngredientRepositoryInterface;
use App\Models\Ingredient as EloquentIngredient;
use App\Models\Product as EloquentProduct;

class EloquentIngredientRepository implements IngredientRepositoryInterface
{
    public function findByName(string $name): Ingredient
    {
        $eloquent = EloquentIngredient::where('name', $name)->firstOrFail();
        $ingredient = new Ingredient($eloquent->name, $eloquent->stock, $eloquent->initial_stock);
        $ingredient->setId($eloquent->id);
        if ($eloquent->stock_alert_sent) {
            $ingredient->markAlertSent();
        }

        return $ingredient;
    }

    public function update(Ingredient $ingredient): void
    {
        $eloquent = EloquentIngredient::findOrFail($ingredient->getId());
        $eloquent->stock = $ingredient->getStock();
        $eloquent->stock_alert_sent = $ingredient->hasAlertSent();
        $eloquent->save();
    }

    public function getProductWithIngredients(int $productId): Product
    {
        $eloquentProduct = EloquentProduct::with('ingredients')->findOrFail($productId);
        $ingredients = $eloquentProduct->ingredients->map(function ($ingredient) {
            $domainIngredient = new Ingredient($ingredient->name, $ingredient->stock, $ingredient->initial_stock);
            $domainIngredient->setId($ingredient->id);
            if ($ingredient->stock_alert_sent) {
                $domainIngredient->markAlertSent();
            }

            return [$domainIngredient, $ingredient->pivot->amount];
        })->all();

        return new Product($eloquentProduct->id, $eloquentProduct->name, $ingredients);
    }
}
