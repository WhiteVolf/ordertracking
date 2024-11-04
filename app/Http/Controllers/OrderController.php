<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;

/**
 * @OA\Info(title="Order API", version="1.0")
 * 
 * @OA\Tag(
 *     name="Orders",
 *     description="API Endpoints of Orders"
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get list of orders",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter orders by status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="min_amount",
     *         in="query",
     *         description="Minimum order amount",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="max_amount",
     *         in="query",
     *         description="Maximum order amount",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="product_name",
     *         in="query",
     *         description="Filter orders by product name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Get order by ID",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    // Показати окреме замовлення
    public function show($id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or not authorized'], 404);
        }

        return response()->json($order, 200);
    }
    
    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_name", "order_number", "amount", "status"},
     *             @OA\Property(property="product_name", type="string", example="Test Product"),
     *             @OA\Property(property="order_number", type="string", example="ORD123"),
     *             @OA\Property(property="amount", type="number", example=100),
     *             @OA\Property(property="status", type="string", example="new")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/orders/{id}",
     *     summary="Update an order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_name", type="string", example="Updated Product"),
     *             @OA\Property(property="amount", type="number", example=150),
     *             @OA\Property(property="status", type="string", example="shipped")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     summary="Delete an order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
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
