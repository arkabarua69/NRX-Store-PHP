<?php

namespace App\Services;

use App\Constants\OrderStatus;
use App\Constants\Role;
use App\Constants\Status;
use App\Filters\OrderFilter;
use App\Helpers\ActivityLogger;
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Models\AutoVoucher;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Variation;
use App\Models\Voucher;
use App\Services\Gateway\GatewayInterface;
use App\Services\TopupProvider\Validators\StockValidator;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    public function getMine(array $queryParams = [], bool $isVoucher = false)
    {
        $queryBuilder = Order::with(['variation', 'product', 'voucher'])
            ->where('user_id', user_id())
            ->latest();

        if ($isVoucher) {
            $queryBuilder->whereHas('product', function (Builder $query) {
                $query->where('type', Status::VOUCHER);
            });
        } else {
            $queryBuilder->whereDoesntHave('product', function (Builder $query) {
                $query->where('type', Status::VOUCHER);
            });
        }

        $orders = app(OrderFilter::class)->getResults([
            'builder' => $queryBuilder,
            'params'  => $queryParams,
        ]);

        return $orders;
    }

    public function addOrder(Request $request)
    {
        $variation = Variation::where('stock', '>', 0)
            ->with(['product', 'vouchers' => function ($query) {
                $query->where('status', Status::AVAILABLE);
            }])
            ->findOrFail($request->variation_id);

        if ($variation->product->isVoucher() && $variation->vouchers->count() < $request->input('quantity', 1)) {
            return back()->with('error', __('Sorry, this voucher is out of stock.'));
        }

        $amount_cal = $variation->price * $request->input('quantity', 1);
        $variation_buy_rate = $variation->buy_rate;
        $profit_cal = 0.00;

        if ($amount_cal > $variation_buy_rate) {
            $profit_cal = $amount_cal - $variation_buy_rate;
            $profit_cal = number_format($profit_cal, 2, '.', '');
        }

        $orderData = [
            'user_id'      => user_id(),
            'product_id'   => $variation->product->id,
            'variation_id' => $variation->id,
            'quantity'     => $request->input('quantity', 1),
            'amount'       => $amount_cal,
            'profit'       => $profit_cal,
            'track_id'     => strRandom(),
        ];

        if (in_array($variation->product->type, [Status::TOPUP, Status::INGAME])) {
            $orderData['account_info'] = $request->input('account_info');
        }

        try {
            $order = DB::transaction(function () use ($orderData) {
                return Order::create($orderData);
            });
        } catch (Exception $e) {
            return back()->with('error', __('Something went wrong.'));
        }

        $order = Order::where('id', $order->id)->orderBy('id', 'DESC')->with(['user', 'variation', 'product'])->first();

        if (gs()->wallet && $request->payment_method === Status::WALLET) {
            try {
                $this->completeOrderWithWallet($order, $request->payment_method);
                $redirect = ($order->product->isVoucher()) ? route('user.codes') : route('user.orders');
                return redirect($redirect)->with('success', __('Order Successful.'));
            } catch (\Exception $exception) {
                return back()->with('error', $exception->getMessage());
            }
        }

        try {
            $gateway = $request->input('gateway', 'uddoktapay');
            $getwayObj = 'App\\Services\\Gateway\\' . $gateway . '\\Payment';
            $getwayObj = app($getwayObj);
            if (!($getwayObj instanceof GatewayInterface)) {
                throw new Exception('The Payment gateway must implement GatewayInterface.');
            }
            $data = $getwayObj::prepareOrderData($order, $gateway);
            $data = (object) $data;
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if (isset($data->error)) {
            return back()->with('error', $data->message);
        }

        if (isset($data->redirect_url)) {
            return redirect($data->redirect_url);
        }

        $page_title = 'Payment Confirm';
        return view($data->view, compact('data', 'page_title', 'order'));
    }

    public function payNow($orderId)
    {
        $order = Order::where('id', $orderId)->orderBy('id', 'DESC')->with(['user', 'variation', 'product'])->first();

        if (!$order) {
            return back()->with('error', __('Order not found.'));
        }

        $variation = Variation::where('stock', '>', 0)->with(['product', 'vouchers' => function ($query) {
            $query->where('status', Status::AVAILABLE);
        }])->find($order->variation_id);

        if (!$variation) {
            return back()->with('error', __('Sorry, this product is out of stock.'));
        }

        if ($variation->product->isVoucher() && $variation->vouchers->count() < 1) {
            return back()->with('error', __('Sorry, this voucher is out of stock.'));
        }

        if (gs()->wallet === Status::ACTIVE && auth()->user()->balance >= $order->amount) {
            try {
                $this->completeOrderWithWallet($order, Status::WALLET);
                $redirect = ($order->product->isVoucher()) ? route('user.codes') : route('user.orders');
                return redirect($redirect)->with('success', __('Order Successful.'));
            } catch (\Exception $exception) {
                return back()->with('error', $exception->getMessage());
            }
        }

        try {
            $gateway = request('gateway', 'uddoktapay');
            $getwayObj = 'App\\Services\\Gateway\\' . $gateway . '\\Payment';
            $getwayObj = app($getwayObj);
            if (!($getwayObj instanceof GatewayInterface)) {
                throw new Exception('The Payment gateway must implement GatewayInterface.');
            }
            $data = $getwayObj::prepareOrderData($order, $gateway);
            $data = (object) $data;
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if (isset($data->error)) {
            return back()->with('error', $data->message);
        }

        if (isset($data->redirect_url)) {
            return redirect($data->redirect_url);
        }

        $page_title = 'Payment Confirm';
        return view($data->view, compact('data', 'page_title', 'order'));
    }

    public function gatewayIpn(Request $request, string $trx, string $gateway)
    {
        try {
            $order = Order::where('track_id', $trx)->orderBy('id', 'DESC')->with(['user', 'variation', 'product'])->first();
            if (!$order) {
                throw new \Exception(__('Order not found.'));
            }

            $getwayObj = 'App\\Services\\Gateway\\' . $gateway . '\\Payment';
            $getwayObj = app($getwayObj);
            if (!($getwayObj instanceof GatewayInterface)) {
                throw new Exception('The Payment gateway must implement GatewayInterface.');
            }
            $data = $getwayObj::orderIpn($request, $order, $gateway);
        } catch (\Exception $exception) {
            return redirect()->route('user.account')->with('error', $exception->getMessage());
        }

        if (isset($data['redirect'])) {
            return redirect($data['redirect'])->with($data['status'], $data['message']);
        }
    }

    public function completeOrder(Order $order, string $paymentMethod, string $transactionId, ?Voucher $vouchers = null)
    {
        $exists = Transaction::where('transaction_id', $transactionId)->exists();
        if ($exists) {
            throw new \Exception(__('Transaction ID already exists.'));
        }

        $variation = Variation::where('stock', '>', 0)->with(['product', 'vouchers' => function ($query) {
            $query->where('status', Status::AVAILABLE);
        }])->find($order->variation_id);

        if (!$variation) {
            $this->completeDeposit($order, $paymentMethod, $transactionId);
            throw new \Exception(__('Sorry, this product is out of stock. Your payment amount has been added to your wallet.'));
        }

        $stockValidator = app(StockValidator::class);
        if (!$stockValidator->validateBeforePlacement($order)) {
            $this->completeDeposit($order, $paymentMethod, $transactionId);
            throw new \Exception(__('Sorry, this product is currently out of stock with provider. Your payment amount has been added to your wallet.'));
        }

        if ($order->product->isVoucher()) {
            $vouchers = Voucher::where('status', Status::AVAILABLE)
                ->where('variation_id', $order->variation_id)
                ->limit($order->quantity)
                ->orderBy('id', 'DESC')
                ->get();

            if ($vouchers->count() < $order->quantity) {
                throw new \Exception(__('Insufficient vouchers available.'));
            }
        }

        DB::transaction(function () use ($order, $paymentMethod, $transactionId, $vouchers) {
            if ($order->isPending()) {
                $newStatus = ($order->product->isVoucher()) ? Status::COMPLETED : Status::PROCESSING;
                $order['status'] = $newStatus;
                $order->update();

                ActivityLogger::log(
                    "Order #{$order->id} completed via {$paymentMethod}",
                    $order,
                    logName: 'order',
                    event: 'completed',
                    properties: [
                        'amount' => $order->amount,
                        'payment_method' => $paymentMethod,
                        'transaction_id' => $transactionId,
                    ]
                );

                if ($order->product->isVoucher()) {
                    $variation = $order->variation;
                    $variation->stock -= $vouchers->count();
                    $variation->save();

                    $voucherCodes = [];
                    foreach ($vouchers as $index => $voucher) {
                        $voucherCodes[] = is_array($voucher->code) ? implode(',', $voucher->code) : $voucher->code;
                        $voucher->status = Status::SOLD;
                        $voucher->order_id = $order->id;
                        $voucher->save();

                        if ($index < $order->quantity - 1) {
                            $voucherCodes[] = ',';
                        }
                    }

                    $order['voucher_code'] = implode('', $voucherCodes);
                    $order->update();
                } else {
                    $variation = $order->variation;
                    $variation->stock -= 1;
                    $variation->save();
                }

                $transaction = new Transaction();
                $transaction->user_id       = $order->user_id;
                $transaction->order_id      = $order->id;
                $transaction->trx_type      = Status::CREDIT;
                $transaction->amount        = $order->amount;
                $transaction->payment_method = $paymentMethod;
                $transaction->remarks       = "Product purchased via {$paymentMethod}, Order ID: {$order->id}";
                $transaction->transaction_id = $transactionId;
                $transaction->save();

                $this->addTransactionId($order->id, $transactionId);
                $this->handleReseller($order);

                try {
                    if (gs()->smtp_from_address && gs()->smtp_host && gs()->smtp_password && gs()->smtp_port && gs()->smtp_username) {
                        foreach (User::where('role', Role::ADMIN)->cursor() as $admin) {
                            Mail::to($admin->email)->queue(new OrderPlaced($order));
                        }
                        Mail::to($order->user->email)->queue(new OrderStatusChanged($order));
                    }
                } catch (Exception $e) {
                    Log::warning('Order notification mail failed', ['order' => $order->id, 'error' => $e->getMessage()]);
                }

                try {
                    $autoVoucher = AutoVoucher::where('status', Status::AVAILABLE)
                        ->where('variation_id', $order->variation_id)
                        ->first();

                    if ($order->product->isTopup() && $order->variation->isAutomatic() && $autoVoucher && gs()->enable_auto_topup && gs()->free_fire_server_url && gs()->free_fire_server_api_key) {
                        $order->status = OrderStatus::AUTOPROCESSING;
                        $order->voucher_code = $autoVoucher->code;
                        $order->save();

                        $autoVoucher->order_id = $order->id;
                        $autoVoucher->status = Status::SOLD;
                        $autoVoucher->save();

                        ActivityLogger::log(
                            "Order #{$order->id} sent for auto-processing via {$order->variation->provider}",
                            $order,
                            logName: 'order',
                            event: 'auto_processing',
                            properties: ['provider' => $order->variation->provider]
                        );
                    }
                } catch (Exception $e) {
                    Log::warning('Auto-topup dispatch failed', ['order' => $order->id, 'error' => $e->getMessage()]);
                }
            }
        }, 5);
    }

    public function completeOrderWithWallet(Order $order, string $paymentMethod, ?Voucher $vouchers = null)
    {
        if ($order->amount > auth()->user()->balance) {
            throw new \Exception(__('Insufficient Balance.'));
        }

        $variation = Variation::where('stock', '>', 0)->with(['product', 'vouchers' => function ($query) {
            $query->where('status', Status::AVAILABLE);
        }])->find($order->variation_id);

        if (!$variation) {
            throw new \Exception(__('Sorry, this product is out of stock.'));
        }

        if ($order->product->isVoucher()) {
            $vouchers = Voucher::where('status', Status::AVAILABLE)
                ->where('variation_id', $order->variation_id)
                ->limit($order->quantity)
                ->orderBy('id', 'DESC')
                ->get();

            if ($vouchers->count() < $order->quantity) {
                throw new \Exception(__('Insufficient vouchers available.'));
            }
        }

        DB::transaction(function () use ($order, $vouchers, $paymentMethod) {
            if ($order->isPending()) {
                // Update Order status
                $order['status'] = ($order->product->isVoucher()) ? Status::COMPLETED : Status::PROCESSING;
                $order->update();

                // Deduct wallet balance
                $user = $order->user;
                $user->balance -= $order->amount;
                $user->save();

                if ($order->product->isVoucher()) {
                    // Update Variation stock
                    $variation = $order->variation;
                    $variation->stock -= $vouchers->count();
                    $variation->save();

                    // Assign voucher codes to order
                    $voucherCodes = [];
                    foreach ($vouchers as $index => $voucher) {
                        $voucherCodes[] = is_array($voucher->code) ? implode(',', $voucher->code) : $voucher->code;
                        $voucher->status = Status::SOLD;
                        $voucher->order_id = $order->id;
                        $voucher->save();

                        if ($index < $order->quantity - 1) {
                            $voucherCodes[] = ',';
                        }
                    }

                    $order['voucher_code'] = implode('', $voucherCodes);
                    $order->update();
                } else {
                    // Update Variation stock
                    $variation = $order->variation;
                    $variation->stock -= 1;
                    $variation->save();
                }

                // Add Transaction
                $transactionId = strRandom();
                $transaction = new Transaction();
                $transaction->user_id        = $order->user_id;
                $transaction->order_id       = $order->id;
                $transaction->trx_type       = Status::CREDIT;
                $transaction->amount         = $order->amount;
                $transaction->payment_method = $paymentMethod;
                $transaction->remarks        = "Product purchased via {$paymentMethod}, Order ID: {$order->id}";
                $transaction->transaction_id = $transactionId;
                $transaction->save();

                $this->addTransactionId($order->id, $transactionId);
                $this->handleReseller($order);

                try {
                    if (gs()->smtp_from_address && gs()->smtp_host && gs()->smtp_password && gs()->smtp_port && gs()->smtp_username) {
                        foreach (User::where('role', Role::ADMIN)->cursor() as $admin) {
                            Mail::to($admin->email)->queue(new OrderPlaced($order));
                        }
                    }
                } catch (Exception $e) {
                    // Mail failure should not break the order
                }

                try {
                    $autoVoucher = AutoVoucher::where('status', Status::AVAILABLE)
                        ->where('variation_id', $order->variation_id)
                        ->first();

                    if ($order->product->isTopup() && $order->variation->isAutomatic() && $autoVoucher && gs()->enable_auto_topup && gs()->free_fire_server_url && gs()->free_fire_server_api_key) {
                        $order->status = OrderStatus::AUTOPROCESSING;
                        $order->voucher_code = $autoVoucher->code;
                        $order->save();

                        $autoVoucher->order_id = $order->id;
                        $autoVoucher->status = Status::SOLD;
                        $autoVoucher->save();
                    }
                } catch (Exception $e) {
                    // Auto-topup failure should not break the order
                }
            }
        }, 5);
    }

    private function completeDeposit(Order $order, string $paymentMethod, string $transactionId)
    {
        DB::transaction(function () use ($order, $paymentMethod, $transactionId) {
            // Credit wallet
            $user = $order->user;
            $user->balance += $order->amount;
            $user->save();

            // Create Deposit record
            $deposit = new Deposit();
            $deposit->user_id  = $order->user_id;
            $deposit->amount   = $order->amount;
            $deposit->track_id = strRandom();
            $deposit->status   = Status::PAID;
            $deposit->save();

            // Add Transaction
            $transaction = new Transaction();
            $transaction->user_id        = $order->user_id;
            $transaction->deposit_id     = $deposit->id;
            $transaction->trx_type       = Status::DEBIT;
            $transaction->amount         = $order->amount;
            $transaction->payment_method = $paymentMethod;
            $transaction->remarks        = 'Wallet topped up due to out-of-stock product via ' . $paymentMethod;
            $transaction->transaction_id = $transactionId;
            $transaction->save();

            $this->addTransactionId($order->id, $transactionId);
        }, 5);
    }

    public static function cancelOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            $user = $order->user;
            $refundAmount = $order->amount;

            if ($user->isReseller()) {
                $percentageAmount = getPercentageAmount($order->amount, $order->product->percentage);
                $refundAmount -= $percentageAmount;
            }

            $user->balance += $refundAmount;
            $user->save();

            if ($order->product->isVoucher() && $order->voucher_code) {
                $voucher = Voucher::where('order_id', $order->id)->first();
                if ($voucher) {
                    $voucher->status = Status::AVAILABLE;
                    $voucher->order_id = null;
                    $voucher->transaction_id = null;
                    $voucher->save();

                    $variation = $order->variation;
                    $variation->stock += 1;
                    $variation->save();
                }
            }

            if ($order->status === OrderStatus::PROCESSING || $order->status === OrderStatus::AUTOPROCESSING) {
                $autoVoucher = AutoVoucher::where('order_id', $order->id)->first();
                if ($autoVoucher) {
                    $autoVoucher->status = Status::AVAILABLE;
                    $autoVoucher->order_id = null;
                    $autoVoucher->transaction_id = null;
                    $autoVoucher->save();

                    $variation = $order->variation;
                    $variation->stock += 1;
                    $variation->save();
                }
            }

            $order->status = Status::CANCELLED;
            $order->delivery_message = 'Order cancelled and refunded.';
            $order->save();

            ActivityLogger::log(
                "Order #{$order->id} cancelled. Refunded {$refundAmount} to user wallet.",
                $order,
                logName: 'order',
                event: 'cancelled',
                properties: ['refund_amount' => $refundAmount]
            );

            $transaction = new Transaction();
            $transaction->user_id        = $order->user_id;
            $transaction->order_id       = $order->id;
            $transaction->trx_type       = Status::DEBIT;
            $transaction->amount         = $refundAmount;
            $transaction->payment_method = Status::WALLET;
            $transaction->remarks        = 'Refund for cancelled order ID: ' . $order->id;
            $transaction->transaction_id = strRandom();
            $transaction->save();

            try {
                if (gs()->smtp_from_address && gs()->smtp_host && gs()->smtp_password && gs()->smtp_port && gs()->smtp_username) {
                    Mail::to($order->user->email)->queue(new OrderStatusChanged($order));
                }
            } catch (Exception $e) {
                Log::warning('Refund notification failed', ['order' => $order->id, 'error' => $e->getMessage()]);
            }
        }, 5);
    }

    private function handleReseller(Order $order)
    {
        $user = $order->user;
        if ($user->isReseller()) {
            $percentageAmount = getPercentageAmount($order->amount, $order->product->percentage);
            $user->balance += $percentageAmount;
            $user->save();

            $transaction = new Transaction();
            $transaction->user_id        = $order->user_id;
            $transaction->order_id       = $order->id;
            $transaction->trx_type       = Status::DEBIT;
            $transaction->amount         = $percentageAmount;
            $transaction->payment_method = Status::WALLET;
            $transaction->remarks        = 'Reseller bonus for order ID: ' . $order->id;
            $transaction->transaction_id = strRandom();
            $transaction->save();
        }
    }

    private function addTransactionId(int $order_id, string $transactionId): void
    {
        $vouchers = Voucher::where('order_id', $order_id)->get();
        $autovouchers = AutoVoucher::where('order_id', $order_id)->get();

        if ($vouchers->count() > 0) {
            foreach ($vouchers as $voucher) {
                $voucher->transaction_id = $transactionId;
                $voucher->save();
            }
        } elseif ($autovouchers->count() > 0) {
            foreach ($autovouchers as $autovoucher) {
                $autovoucher->transaction_id = $transactionId;
                $autovoucher->save();
            }
        }
    }
}
