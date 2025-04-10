<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $burger = Product::create(['id' => 1, 'name' => 'Burger']);
        $beef = Ingredient::create(['name' => 'Beef', 'stock' => 20000, 'initial_stock' => 20000]);
        $cheese = Ingredient::create(['name' => 'Cheese', 'stock' => 5000, 'initial_stock' => 5000]);
        $onion = Ingredient::create(['name' => 'Onion', 'stock' => 1000, 'initial_stock' => 1000]);
        $burger->ingredients()->attach([
            $beef->id => ['amount' => 150],
            $cheese->id => ['amount' => 30],
            $onion->id => ['amount' => 20],
        ]);
    }
}
