<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    // Отримати всі замовлення для авторизованого користувача
    public function index()
    {
        // Отримуємо всі замовлення для авторизованого користувача
        $query = Order::where('user_id', Auth::id());

        // Додаємо фільтрацію за статусом
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Додаємо фільтрацію за мінімальною сумою
        if ($request->has('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        // Додаємо фільтрацію за максимальною сумою
        if ($request->has('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Додаємо фільтрацію за назвою продукту
        if ($request->has('product_name')) {
            $query->where('product_name', 'like', '%' . $request->product_name . '%');
        }

        // Додаємо пагінацію з кількістю записів на сторінку
        $orders = $query->paginate(10);

        return response()->json($orders, 200);
    }

    // Показати окреме замовлення
    public function show($id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or not authorized'], 404);
        }

        return response()->json($order, 200);
    }
    
    // Метод для створення нового замовлення
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'order_number' => 'required|string|unique:orders,order_number',
            'amount' => 'required|numeric',
            'status' => 'required|in:new,processing,shipped,delivered',
        ]);

        $order = Order::create([
            'user_id' => Auth::id(),
            'product_name' => $request->product_name,
            'order_number' => $request->order_number,
            'amount' => $request->amount,
            'status' => $request->status,
        ]);

        // Відправка сповіщення про створення замовлення
        Notification::send(Auth::user(), new OrderCreatedNotification($order));

        return response()->json($order, 201);
    }

    // Метод для редагування існуючого замовлення
    public function update(Request $request, $id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or not authorized'], 404);
        }

        $oldStatus = $order->status;

        $request->validate([
            'product_name' => 'string|max:255',
            'amount' => 'numeric',
            'status' => 'in:new,processing,shipped,delivered',
        ]);

        $order->update($request->only(['product_name', 'amount', 'status']));

        // Відправка сповіщення про зміну статусу, якщо він змінений
        if ($request->status && $request->status !== $oldStatus) {
            Notification::send(Auth::user(), new OrderStatusUpdatedNotification($order));
        }

        return response()->json($order, 200);
    }

    // Видалити замовлення
    public function destroy($id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or not authorized'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
}
