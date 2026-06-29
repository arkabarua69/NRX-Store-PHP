<?php

namespace App\Http\Controllers\Api;

use App\Constants\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Variation;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ResellerApiController
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function products()
    {
        $products = Product::with(['variations' => function ($q) {
            $q->where('stock', '>', 0);
        }])->where('status', 1)->orderBy('order_column')->get();

        return response()->json([
            'status' => 'success',
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'type' => $product->type,
                    'image_url' => $product->image_url,
                    'variations' => $product->variations->map(function ($variation) {
                        return [
                            'id' => $variation->id,
                            'title' => $variation->title,
                            'price' => (float) $variation->price,
                            'stock' => $variation->stock,
                            'automatic' => $variation->automatic,
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variation_id' => 'required|exists:variations,id',
            'quantity' => 'integer|min:1|max:10',
            'account_info' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();
        if (!$user->isReseller() && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. Reseller access required.'], 403);
        }

        $variation = Variation::where('stock', '>', 0)
            ->with('product')
            ->findOrFail($request->variation_id);

        $quantity = $request->input('quantity', 1);
        $amount = $variation->price * $quantity;

        try {
            $order = DB::transaction(function () use ($user, $variation, $quantity, $amount, $request) {
                $profit = 0.00;
                if ($amount > ($variation->buy_rate * $quantity)) {
                    $profit = $amount - ($variation->buy_rate * $quantity);
                }

                $orderData = [
                    'user_id' => $user->id,
                    'product_id' => $variation->product_id,
                    'variation_id' => $variation->id,
                    'quantity' => $quantity,
                    'amount' => $amount,
                    'profit' => number_format($profit, 2, '.', ''),
                    'track_id' => strRandom(),
                    'account_info' => $request->input('account_info'),
                ];

                $order = Order::create($orderData);

                return $order;
            });

            $order->load(['product', 'variation']);
            $this->orderService->completeOrderWithWallet($order, 'wallet');

            return response()->json([
                'status' => 'success',
                'message' => 'Order placed successfully.',
                'data' => [
                    'order_id' => $order->id,
                    'track_id' => $order->track_id,
                    'amount' => (float) $order->amount,
                    'status' => $order->status,
                    'product' => $order->product->title,
                    'variation' => $order->variation->title,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('An error occurred processing your order.')], 500);
        }
    }

    public function orderStatus(Request $request, $trackId)
    {
        $order = Order::where('track_id', $trackId)
            ->with(['product', 'variation'])
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
        }

        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'order_id' => $order->id,
                'track_id' => $order->track_id,
                'amount' => (float) $order->amount,
                'status' => $order->status,
                'product' => $order->product->title,
                'variation' => $order->variation->title,
                'voucher_code' => $order->voucher_code,
                'delivery_message' => $order->delivery_message,
                'created_at' => $order->created_at->toIso8601String(),
                'updated_at' => $order->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function myOrders(Request $request)
    {
        $orders = Order::with(['product', 'variation'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
            ],
        ]);
    }

    public function balance(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'balance' => (float) $request->user()->balance,
                'currency' => gs()->currency_symbol,
            ],
        ]);
    }
}
