<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CodeController extends Controller
{
    public function index(Request $request, OrderService $orderService)
    {
        $codes = $orderService->getMine($request->query(), TRUE);
        return view('user.codes', compact('codes'));
    }
}
