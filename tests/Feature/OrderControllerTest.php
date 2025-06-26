<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Створимо тестових користувачів
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
    }

    /** @test */
    public function user_can_view_their_orders_with_pagination_and_filters()
    {
        $this->actingAs($this->user);

        // Створимо кілька замовлень для поточного користувача
        Order::factory()->count(15)->create(['user_id' => $this->user->id, 'status' => 'new']);
        Order::factory()->count(5)->create(['user_id' => $this->user->id, 'status' => 'shipped']);

        // Виконуємо запит з фільтрацією та пагінацією
        $response = $this->getJson('/api/orders?status=new&page=1');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data'); // Перша сторінка повинна мати 10 записів
    }

    /** @test */
    public function user_can_create_an_order()
    {
        $this->actingAs($this->user);

        $orderData = [
            'product_name' => 'Test Product',
            'order_number' => 'ORD123',
            'amount' => 100,
            'status' => 'new',
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201);
        $response->assertJsonFragment($orderData);
        $this->assertDatabaseHas('orders', ['order_number' => 'ORD123']);
    }

    /** @test */
    public function user_can_view_a_single_order()
    {
        $this->actingAs($this->user);

        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['order_number' => $order->order_number]);
    }

    /** @test */
    public function user_can_update_their_order()
    {
        $this->actingAs($this->user);

        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $updatedData = [
            'product_name' => 'Updated Product',
            'amount' => 150,
            'status' => 'shipped',
        ];

        $response = $this->putJson("/api/orders/{$order->id}", $updatedData);

        $response->assertStatus(200);
        $response->assertJsonFragment($updatedData);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'shipped']);
    }

    /** @test */
    public function user_can_delete_their_order()
    {
        $this->actingAs($this->user);

        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /** @test */
    public function user_cannot_view_others_orders()
    {
        $this->actingAs($this->user);

        $order = Order::factory()->create(['user_id' => $this->anotherUser->id]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(404); // Користувач не має доступу до чужого замовлення
    }

    /** @test */
    public function user_cannot_update_others_orders()
    {
        $this->actingAs($this->user);

        $order = Order::factory()->create(['user_id' => $this->anotherUser->id]);

        $updatedData = [
            'product_name' => 'Unauthorized Update',
            'amount' => 200,
        ];

        $response = $this->putJson("/api/orders/{$order->id}", $updatedData);

        $response->assertStatus(404); // Користувач не має права оновлювати чужі замовлення
    }

    /** @test */
    public function user_cannot_delete_others_orders()
    {
        $this->actingAs($this->user);

        $order = Order::factory()->create(['user_id' => $this->anotherUser->id]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(404); // Користувач не може видаляти чужі замовлення
    }

    /** @test */
    public function unauthenticated_user_cannot_access_orders()
    {
        // Спроба перегляду замовлень без авторизації
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401); // Неавторизований доступ заборонений
    }

    /** @test */
    public function authenticated_user_can_export_orders_to_excel()
    {
        $this->actingAs($this->user);

        $response = $this->get('/api/orders/export-excel');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function authenticated_user_can_export_orders_to_csv()
    {
        $this->actingAs($this->user);

        $response = $this->get('/api/orders/export-csv');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv');
    }

    /** @test */
    public function authenticated_user_can_export_orders_to_pdf()
    {
        $this->actingAs($this->user);

        $response = $this->get('/api/orders/export-pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
