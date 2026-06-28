<?php

namespace App\Http\Controllers;

use App\Services\DepositService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function depositIpn(Request $request, DepositService $depositService, string $trx, string $gateway)
    {
        return $depositService->gatewayIpn($request, $trx, $gateway);
    }

    public function depositCancel()
    {
        return redirect(route('user.addfunds'))->with('error', 'Payment has been cancelled.');
    }

    public function orderIpn(Request $request, OrderService $orderService, string $trx, string $gateway)
    {
        return $orderService->gatewayIpn($request, $trx, $gateway);
    }

    public function orderCancel()
    {
        return redirect(route('user.orders'))->with('error', 'Payment has been cancelled.');
    }

    public function codeCancel()
    {
        return redirect(route('user.codes'))->with('error', 'Payment has been cancelled.');
    }
}
