<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    /** @test */
    public function user_can_add_item_to_cart_and_checkout()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cart_items', ['user_id' => $this->user->id]);

        $checkout = $this->postJson('/api/cart/checkout');
        $checkout->assertStatus(201);
        $this->assertDatabaseHas('orders', ['user_id' => $this->user->id, 'quantity' => 3]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    /** @test */
    public function user_cannot_add_more_than_five_items()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 6,
        ]);

        $response->assertStatus(422);
    }
}
