<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Показати всі товари
    public function index()
    {
        return response()->json(Product::all());
    }

    // Створити товар
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string|nullable',
            'price' => 'required|numeric',
        ]);

        $product = Product::create($request->only(['name', 'description', 'price']));

        return response()->json($product, 201);
    }

    // Показати окремий товар
    public function show(Product $product)
    {
        return response()->json($product);
    }

    // Оновити товар
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'string',
            'description' => 'string|nullable',
            'price' => 'numeric',
        ]);

        $product->update($request->only(['name', 'description', 'price']));

        return response()->json($product);
    }

    // Видалити товар
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }
}
