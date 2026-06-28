<?php

namespace App\Services\Gateway;

use Exception;
use App\Models\Order;
use App\Models\Deposit;
use Illuminate\Http\Request;

interface GatewayInterface
{
    public static function prepareDepositData(Deposit $deposit, $gateway): array | Exception;
    public static function depositIpn(Request $request, Deposit $deposit, $gateway): array | Exception;
    public static function prepareOrderData(Order $order, $gateway): array | Exception;
    public static function orderIpn(Request $request, Order $order, $gateway): array | Exception;
}
