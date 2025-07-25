<?php

namespace Tests\Unit;

use App\Http\Controllers\OrderController;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Services\ReverbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class OrderControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_store_creates_order_and_sends_notification(): void
    {
        Notification::fake();
        $reverb = Mockery::mock(ReverbService::class);
        $reverb->shouldReceive('sendNotification')->once();

        $controller = new OrderController($reverb);

        $request = Request::create('/api/orders', 'POST', [
            'product_id' => $this->product->id,
            'order_number' => 'ORD111',
            'amount' => 50,
            'status' => 'new',
            'quantity' => 2,
        ]);

        $response = $controller->store($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertDatabaseHas('orders', ['order_number' => 'ORD111']);
        Notification::assertSentTo($this->user, OrderCreatedNotification::class);
    }

    public function test_update_changes_order_and_sends_notification_when_status_changed(): void
    {
        Notification::fake();
        $reverb = Mockery::mock(ReverbService::class);
        $reverb->shouldReceive('sendNotification')->once();

        $controller = new OrderController($reverb);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'new',
        ]);

        $request = Request::create("/api/orders/{$order->id}", 'PUT', [
            'status' => 'shipped',
        ]);

        $response = $controller->update($request, $order->id);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'shipped']);
        Notification::assertSentTo($this->user, OrderStatusUpdatedNotification::class);
    }

    public function test_destroy_deletes_order(): void
    {
        $reverb = Mockery::mock(ReverbService::class);
        $controller = new OrderController($reverb);

        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $controller->destroy($order->id);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_show_returns_order_for_authenticated_user(): void
    {
        $reverb = Mockery::mock(ReverbService::class);
        $controller = new OrderController($reverb);

        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $controller->show($order->id);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals($order->order_number, $data['order_number']);
    }

    public function test_index_returns_paginated_orders_filtered_by_status(): void
    {
        $reverb = Mockery::mock(ReverbService::class);
        $controller = new OrderController($reverb);

        Order::factory()->count(5)->create(['user_id' => $this->user->id, 'status' => 'new']);
        Order::factory()->count(3)->create(['user_id' => $this->user->id, 'status' => 'shipped']);

        $request = Request::create('/api/orders', 'GET', ['status' => 'new']);

        $response = $controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals(5, $data['total']);
    }
}

