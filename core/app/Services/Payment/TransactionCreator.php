<?php

namespace App\Services\Payment;

use App\Models\Transaction;

class TransactionCreator
{
    public static function create(array $attributes): Transaction
    {
        $transaction = new Transaction();
        $transaction->user_id = $attributes['user_id'];
        $transaction->order_id = $attributes['order_id'] ?? null;
        $transaction->deposit_id = $attributes['deposit_id'] ?? null;
        $transaction->trx_type = $attributes['trx_type'];
        $transaction->amount = $attributes['amount'];
        $transaction->payment_method = $attributes['payment_method'];
        $transaction->remarks = $attributes['remarks'];
        $transaction->transaction_id = $attributes['transaction_id'] ?? strRandom();
        $transaction->save();

        return $transaction;
    }

    public static function existsById(string $transactionId): bool
    {
        return Transaction::where('transaction_id', $transactionId)->exists();
    }
}
