<?php

namespace App\Services;

use App\Constants\OrderStatus;
use App\Constants\Status;
use App\Filters\OrderFilter;
use App\Helpers\ActivityLogger;
use App\Models\AutoVoucher;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\User;
use App\Models\Variation;
use App\Models\Voucher;
use App\Services\Payment\AutoTopupDispatcher;
use App\Services\Payment\OrderNotifier;
use App\Services\Payment\TransactionCreator;
use App\Services\Payment\VoucherFulfillment;
use App\Services\TopupProvider\Validators\StockValidator;
use App\Services\Traits\ResolvesGateway;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderService
{
    use ResolvesGateway;

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
            'params' => $queryParams,
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
            'user_id' => user_id(),
            'product_id' => $variation->product->id,
            'variation_id' => $variation->id,
            'quantity' => $request->input('quantity', 1),
            'amount' => $amount_cal,
            'profit' => $profit_cal,
            'track_id' => strRandom(),
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
                return back()->with('error', __('Payment processing failed.'));
            }
        }

        try {
            $gateway = $request->input('gateway', 'uddoktapay');
            $gatewayObj = $this->resolveGateway($gateway);
            $data = $gatewayObj::prepareOrderData($order, $gateway);
            $data = (object) $data;
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return $this->handleGatewayResponse($data, compact('order'));
    }

    public function payNow($orderId)
    {
        $order = Order::where('id', $orderId)->orderBy('id', 'DESC')->with(['user', 'variation', 'product'])->first();

        if (! $order) {
            return back()->with('error', __('Order not found.'));
        }

        $variation = Variation::where('stock', '>', 0)->with(['product', 'vouchers' => function ($query) {
            $query->where('status', Status::AVAILABLE);
        }])->find($order->variation_id);

        if (! $variation) {
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
                return back()->with('error', __('Payment processing failed.'));
            }
        }

        try {
            $gateway = request('gateway', 'uddoktapay');
            $gatewayObj = $this->resolveGateway($gateway);
            $data = $gatewayObj::prepareOrderData($order, $gateway);
            $data = (object) $data;
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return $this->handleGatewayResponse($data, compact('order'));
    }

    public function gatewayIpn(Request $request, string $trx, string $gateway)
    {
        try {
            $order = Order::where('track_id', $trx)->orderBy('id', 'DESC')->with(['user', 'variation', 'product'])->first();
            if (! $order) {
                throw new \Exception(__('Order not found.'));
            }

            $gatewayObj = $this->resolveGateway($gateway);
            $data = $gatewayObj::orderIpn($request, $order, $gateway);
        } catch (\Exception $exception) {
            return redirect()->route('user.account')->with('error', __('Payment verification failed.'));
        }

        if (isset($data['redirect'])) {
            return redirect($data['redirect'])->with($data['status'], $data['message']);
        }

        return redirect()->route('user.orders')->with('error', __('Payment verification failed.'));
    }

    public function completeOrder(Order $order, string $paymentMethod, string $transactionId, ?Voucher $vouchers = null)
    {
        $variation = Variation::where('stock', '>', 0)->with(['product', 'vouchers' => function ($query) {
            $query->where('status', Status::AVAILABLE);
        }])->find($order->variation_id);

        if (! $variation) {
            $this->completeDeposit($order, $paymentMethod, $transactionId);
            throw new \Exception(__('Sorry, this product is out of stock. Your payment amount has been added to your wallet.'));
        }

        $stockValidator = app(StockValidator::class);
        if (! $stockValidator->validateBeforePlacement($order)) {
            $this->completeDeposit($order, $paymentMethod, $transactionId);
            throw new \Exception(__('Sorry, this product is currently out of stock with provider. Your payment amount has been added to your wallet.'));
        }

        if ($order->product->isVoucher()) {
            $vouchers = VoucherFulfillment::fetchAvailableVouchers($order->variation_id, $order->quantity);

            if ($vouchers->count() < $order->quantity) {
                throw new \Exception(__('Insufficient vouchers available.'));
            }
        }

        DB::transaction(function () use ($order, $paymentMethod, $transactionId, $vouchers) {
            if (TransactionCreator::existsById($transactionId)) {
                throw new \Exception(__('Transaction ID already exists.'));
            }
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
                    VoucherFulfillment::assignVouchersToOrder($order, $vouchers);
                } else {
                    VoucherFulfillment::decrementStock($order);
                }

                TransactionCreator::create([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'trx_type' => Status::CREDIT,
                    'amount' => $order->amount,
                    'payment_method' => $paymentMethod,
                    'remarks' => "Product purchased via {$paymentMethod}, Order ID: {$order->id}",
                    'transaction_id' => $transactionId,
                ]);

                $this->addTransactionId($order->id, $transactionId);
                $this->handleReseller($order);

                OrderNotifier::notifyOrderCompleted($order);
                AutoTopupDispatcher::dispatchIfEligible($order);
            }
        }, 5);
    }

    public function completeOrderWithWallet(Order $order, string $paymentMethod, ?Voucher $vouchers = null)
    {
        $variation = Variation::where('stock', '>', 0)->with(['product', 'vouchers' => function ($query) {
            $query->where('status', Status::AVAILABLE);
        }])->find($order->variation_id);

        if (! $variation) {
            throw new \Exception(__('Sorry, this product is out of stock.'));
        }

        if ($order->product->isVoucher()) {
            $vouchers = VoucherFulfillment::fetchAvailableVouchers($order->variation_id, $order->quantity);

            if ($vouchers->count() < $order->quantity) {
                throw new \Exception(__('Insufficient vouchers available.'));
            }
        }

        DB::transaction(function () use ($order, $vouchers, $paymentMethod) {
            $user = User::where('id', $order->user_id)->lockForUpdate()->first();
            if ($order->amount > $user->balance) {
                throw new \Exception(__('Insufficient Balance.'));
            }
            if ($order->isPending()) {
                $order['status'] = ($order->product->isVoucher()) ? Status::COMPLETED : Status::PROCESSING;
                $order->update();

                $user->balance -= $order->amount;
                $user->save();

                if ($order->product->isVoucher()) {
                    VoucherFulfillment::assignVouchersToOrder($order, $vouchers);
                } else {
                    VoucherFulfillment::decrementStock($order);
                }

                $transactionId = strRandom();

                TransactionCreator::create([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'trx_type' => Status::CREDIT,
                    'amount' => $order->amount,
                    'payment_method' => $paymentMethod,
                    'remarks' => "Product purchased via {$paymentMethod}, Order ID: {$order->id}",
                    'transaction_id' => $transactionId,
                ]);

                $this->addTransactionId($order->id, $transactionId);
                $this->handleReseller($order);

                OrderNotifier::notifyAdminsOrderPlaced($order);
                AutoTopupDispatcher::dispatchIfEligible($order);
            }
        }, 5);
    }

    private function completeDeposit(Order $order, string $paymentMethod, string $transactionId)
    {
        DB::transaction(function () use ($order, $paymentMethod, $transactionId) {
            $user = $order->user;
            $user->balance += $order->amount;
            $user->save();

            $deposit = new Deposit();
            $deposit->user_id = $order->user_id;
            $deposit->amount = $order->amount;
            $deposit->track_id = strRandom();
            $deposit->status = Status::PAID;
            $deposit->save();

            TransactionCreator::create([
                'user_id' => $order->user_id,
                'deposit_id' => $deposit->id,
                'trx_type' => Status::DEBIT,
                'amount' => $order->amount,
                'payment_method' => $paymentMethod,
                'remarks' => 'Wallet topped up due to out-of-stock product via '.$paymentMethod,
                'transaction_id' => $transactionId,
            ]);

            $this->addTransactionId($order->id, $transactionId);
        }, 5);
    }

    public static function cancelOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            $user = User::where('id', $order->user_id)->lockForUpdate()->first();
            $refundAmount = $order->amount;

            if ($user->isReseller()) {
                $percentageAmount = getPercentageAmount($order->amount, $order->product->percentage);
                $refundAmount -= $percentageAmount;
            }

            $user->balance += $refundAmount;
            $user->save();

            if ($order->product->isVoucher() && $order->voucher_code) {
                $vouchers = Voucher::where('order_id', $order->id)->get();
                foreach ($vouchers as $voucher) {
                    $voucher->status = Status::AVAILABLE;
                    $voucher->order_id = null;
                    $voucher->transaction_id = null;
                    $voucher->save();
                }

                if ($vouchers->count() > 0) {
                    $variation = $order->variation;
                    $variation->stock += $vouchers->count();
                    $variation->save();
                }
            }

            if ($order->status === OrderStatus::PROCESSING || $order->status === OrderStatus::AUTOPROCESSING) {
                $autoVouchers = AutoVoucher::where('order_id', $order->id)->get();
                foreach ($autoVouchers as $autoVoucher) {
                    $autoVoucher->status = Status::AVAILABLE;
                    $autoVoucher->order_id = null;
                    $autoVoucher->transaction_id = null;
                    $autoVoucher->save();
                }

                if ($autoVouchers->count() > 0) {
                    $variation = $order->variation;
                    $variation->stock += $autoVouchers->count();
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

            TransactionCreator::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'trx_type' => Status::DEBIT,
                'amount' => $refundAmount,
                'payment_method' => Status::WALLET,
                'remarks' => 'Refund for cancelled order ID: '.$order->id,
            ]);

            OrderNotifier::notifyUserStatusChanged($order);
        }, 5);
    }

    private function handleReseller(Order $order)
    {
        $user = $order->user;
        if ($user->isReseller()) {
            $percentageAmount = getPercentageAmount($order->amount, $order->product->percentage);
            $user->balance += $percentageAmount;
            $user->save();

            TransactionCreator::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'trx_type' => Status::DEBIT,
                'amount' => $percentageAmount,
                'payment_method' => Status::WALLET,
                'remarks' => 'Reseller bonus for order ID: '.$order->id,
            ]);
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

    private function handleGatewayResponse(object $data, array $viewData): mixed
    {
        if (isset($data->error)) {
            return back()->with('error', $data->message);
        }

        if (isset($data->redirect_url)) {
            return redirect($data->redirect_url);
        }

        $page_title = 'Payment Confirm';

        return view($data->view, array_merge(compact('data', 'page_title'), $viewData));
    }
}
