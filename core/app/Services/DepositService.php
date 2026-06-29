<?php

namespace App\Services;

use App\Constants\Status;
use App\Models\Deposit;
use App\Services\Payment\TransactionCreator;
use App\Services\Traits\ResolvesGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositService
{
    use ResolvesGateway;

    public function addFund(Request $request)
    {
        try {
            $gateway = $request->input('gateway', 'uddoktapay');
            $gatewayObj = $this->resolveGateway($gateway);

            $create = Deposit::create([
                'user_id' => user_id(),
                'amount' => $request->amount,
                'track_id' => strRandom(),
            ]);

            $deposit = Deposit::where('id', $create->id)->orderBy('id', 'DESC')->with(['user'])->first();

            $data = $gatewayObj::prepareDepositData($deposit, $gateway);
            $data = (object) $data;
        } catch (\Exception $exception) {
            return back()->with('error', __('Something went wrong.'));
        }

        return $this->handleGatewayResponse($data, compact('deposit'));
    }

    public function payNow($depositId)
    {
        $deposit = Deposit::where('id', $depositId)
            ->where('user_id', user_id())
            ->orderBy('id', 'DESC')->with(['user'])->first();

        try {
            $gateway = request('gateway', 'uddoktapay');
            $gatewayObj = $this->resolveGateway($gateway);
            $data = $gatewayObj::prepareDepositData($deposit, $gateway);
            $data = (object) $data;
        } catch (\Exception $exception) {
            return back()->with('error', __('Something went wrong.'));
        }

        return $this->handleGatewayResponse($data, compact('deposit'));
    }

    public function gatewayIpn(Request $request, string $trx, string $gateway)
    {
        try {
            $deposit = Deposit::where('track_id', $trx)->orderBy('id', 'desc')->first();
            if (! $deposit) {
                throw new \Exception(__('Deposit ID is not found.'));
            }

            $gatewayObj = $this->resolveGateway($gateway);
            $data = $gatewayObj::depositIpn($request, $deposit, $gateway);
        } catch (\Exception $exception) {
            return redirect()->route('user.addfunds')->with('error', $exception->getMessage());
        }

        if (isset($data['redirect'])) {
            return redirect($data['redirect'])->with($data['status'], $data['message']);
        }

        return redirect()->route('user.addfunds')->with('error', __('Payment verification failed.'));
    }

    public function completeDeposit(Deposit $deposit, string $paymentMethod, string $transactionId)
    {
        DB::transaction(function () use ($deposit, $paymentMethod, $transactionId) {
            if (TransactionCreator::existsById($transactionId)) {
                throw new \Exception(__('Transaction ID already exists.'));
            }
            if ($deposit->status == Status::UNPAID) {
                $deposit['status'] = Status::PAID;
                $deposit->update();

                $user = $deposit->user;
                $user->balance += $deposit->amount;
                $user->save();

                TransactionCreator::create([
                    'user_id' => $deposit->user_id,
                    'deposit_id' => $deposit->id,
                    'trx_type' => Status::DEBIT,
                    'amount' => $deposit->amount,
                    'payment_method' => $paymentMethod,
                    'remarks' => 'Deposit is being made using the '.$paymentMethod,
                    'transaction_id' => $transactionId,
                ]);
            }
        }, 5);
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
