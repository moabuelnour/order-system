<?php

namespace Tests\Feature;

use App\Infrastructure\Mail\StockAlert;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private Product $burger;

    private array $ingredients;

    private function seedTestData(array $ingredientStocks = []): void
    {
        Mail::fake();

        $this->burger = Product::create(['name' => 'Burger']);
        $this->ingredients = [
            'Beef' => Ingredient::create(array_merge(['name' => 'Beef', 'stock' => 20000, 'initial_stock' => 20000], $ingredientStocks['Beef'] ?? [])),
            'Cheese' => Ingredient::create(array_merge(['name' => 'Cheese', 'stock' => 5000, 'initial_stock' => 5000], $ingredientStocks['Cheese'] ?? [])),
            'Onion' => Ingredient::create(array_merge(['name' => 'Onion', 'stock' => 1000, 'initial_stock' => 1000], $ingredientStocks['Onion'] ?? [])),
        ];

        $this->burger->ingredients()->attach([
            $this->ingredients['Beef']->id => ['amount' => 150],
            $this->ingredients['Cheese']->id => ['amount' => 30],
            $this->ingredients['Onion']->id => ['amount' => 20],
        ]);
    }

    private function placeOrder(int $quantity): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/orders', [
            'products' => [['product_id' => $this->burger->id, 'quantity' => $quantity]],
        ]);
    }

    public function test_order_is_stored_and_stock_updated()
    {
        $this->seedTestData();

        $response = $this->placeOrder(2);

        $response->assertStatus(201);
        $response->assertJson([
          'data' => [
              'id' => 1,
              'products' => [
                  [
                      'product_id' => $this->burger->id,
                      'quantity' => 2,
                      'name' => 'Burger',
                  ],
              ],
            ]
        ]);
        $this->assertDatabaseHas('orders', ['id' => 1]);
        $this->assertDatabaseHas('order_product', ['order_id' => 1, 'product_id' => $this->burger->id, 'quantity' => 2]);
        $this->assertDatabaseHas('ingredients', ['name' => 'Beef', 'stock' => 20000 - (150 * 2)]);
        $this->assertDatabaseHas('ingredients', ['name' => 'Cheese', 'stock' => 5000 - (30 * 2)]);
        $this->assertDatabaseHas('ingredients', ['name' => 'Onion', 'stock' => 1000 - (20 * 2)]);
    }

    public function test_email_queued_when_stock_below_50_percent()
    {
        $this->seedTestData(['Onion' => ['stock' => 510, 'stock_alert_sent' => false]]);

        $response = $this->placeOrder(1); // Onion: 510 - 20 = 490

        $response->assertStatus(201);
        $this->assertDatabaseHas('ingredients', [
            'name' => 'Onion',
            'stock' => 490,
            'stock_alert_sent' => true,
        ]);

        Mail::assertQueued(StockAlert::class, function (StockAlert $mail) {
            $envelope = $mail->envelope();

            return $envelope->subject === 'Low Stock Alert: Onion' &&
                   collect($envelope->to)->pluck('address')->contains('merchant@example.com');
        });
    }

    public function test_no_duplicate_email_below_50_percent()
    {
        $this->seedTestData(['Onion' => ['stock' => 490, 'stock_alert_sent' => true]]);

        $response = $this->placeOrder(1); // Onion: 490 - 20 = 470

        $response->assertStatus(201);
        $this->assertDatabaseHas('ingredients', ['name' => 'Onion', 'stock' => 470]);
        Mail::assertNotQueued(StockAlert::class);
    }

    public function test_order_fails_with_insufficient_stock()
    {
        $this->seedTestData(['Onion' => ['stock' => 100, 'stock_alert_sent' => false]]);

        $response = $this->placeOrder(10); // Needs 200g Onion

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Insufficient stock for Onion',
            'errors' => [
                'stock' => ['Insufficient stock for Onion'],
            ],
        ]);
        $this->assertDatabaseMissing('orders', ['id' => 1]);
        $this->assertDatabaseHas('ingredients', ['name' => 'Onion', 'stock' => 100]);
    }
}
