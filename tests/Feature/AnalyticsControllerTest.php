<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_sales_summary()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $product = Product::factory()->create(['price' => 10]);
        $product2 = Product::factory()->create(['price' => 20]);

        Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 30,
            'quantity' => 3,
        ]);
        Order::factory()->create([
            'user_id' => $anotherUser->id,
            'product_id' => $product2->id,
            'amount' => 40,
            'quantity' => 2,
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/analytics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'product_sales' => [['product_id', 'name', 'total_quantity', 'total_amount']],
            'user_orders' => [['user_id', 'name', 'total_orders', 'total_amount']],
        ]);
    }

    /** @test */
    public function sales_summary_can_be_filtered_by_date_range()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10]);

        Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 20,
            'quantity' => 2,
            'created_at' => now()->subDays(2),
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 50,
            'quantity' => 5,
            'created_at' => now()->subDays(10),
        ]);

        $this->actingAs($user);

        $from = now()->subDays(3)->toDateString();
        $to = now()->toDateString();

        $response = $this->getJson("/api/analytics?from={$from}&to={$to}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'total_quantity' => 2,
            'total_amount' => 20,
        ]);
    }
}
