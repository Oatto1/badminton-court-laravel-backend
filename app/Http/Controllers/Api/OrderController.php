<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product; // Assuming we need to look up prices
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->orders()->latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $items = [];

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['id']);
                $totalAmount += $product->price * $itemData['quantity'];
                $items[] = [
                    'product' => $product,
                    'quantity' => $itemData['quantity'],
                    'price' => $product->price
                ];
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'reference_no' => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => 'promptpay', // Default as per OrderResource, or take from request
                'expires_at' => now()->addHour(),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            return response()->json($order->load('items'), 201);
        });
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        return $order->load('items.product');
    }
}
