<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function index(Request $request, OrderService $orderService)
    {
        $orders = $orderService->getMine($request->query());
        return view('user.orders', compact('orders'));
    }

    public function addOrder(Request $request, OrderService $orderService)
    {
        $validator = validator()->make($request->all(), [
            'variation_id' => 'required|exists:variations,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'payment_method' => 'required|string'
        ]);

        if ($validator->fails()) {
            return back()->with('error', __('Variation ID and Payment Method fields are required.'));
        }

        return $orderService->addOrder($request);
    }

    public function payNow(Request $request, OrderService $orderService)
    {
        $validator = validator()->make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->with('error', __('Order ID field is required.'));
        }

        return $orderService->payNow($request->id);
    }
}
