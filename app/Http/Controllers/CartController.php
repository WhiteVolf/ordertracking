<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index()
    {
        $items = CartItem::with('product')->where('user_id', Auth::id())->get();
        return response()->json($items);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $currentTotal = CartItem::where('user_id', Auth::id())->sum('quantity');
        if ($currentTotal + $request->quantity > 5) {
            return response()->json(['error' => 'You can not order more than 5 items'], 422);
        }

        $item = CartItem::firstOrNew([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);
        $newQty = $item->exists ? $item->quantity + $request->quantity : $request->quantity;
        if ($newQty > 5) {
            return response()->json(['error' => 'You can not order more than 5 items'], 422);
        }
        $item->quantity = $newQty;
        $item->save();

        return response()->json($item, 201);
    }

    public function remove(CartItem $item)
    {
        if ($item->user_id !== Auth::id()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }
        $item->delete();
        return response()->json(['message' => 'Item removed']);
    }

    public function checkout()
    {
        $items = CartItem::where('user_id', Auth::id())->get();
        if ($items->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 422);
        }

        $orders = [];
        foreach ($items as $item) {
            $orders[] = Order::create([
                'user_id' => Auth::id(),
                'product_id' => $item->product_id,
                'order_number' => Str::upper(Str::random(8)),
                'amount' => $item->product->price * $item->quantity,
                'status' => 'new',
                'quantity' => $item->quantity,
            ]);
            $item->delete();
        }

        return response()->json($orders, 201);
    }
}
