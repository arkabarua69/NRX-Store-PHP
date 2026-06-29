<?php

namespace Tests\Unit\Models;

use App\Constants\Status;
use App\Models\Transaction;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    public function test_is_credit_returns_true_for_credit_type(): void
    {
        $transaction = new Transaction();
        $transaction->trx_type = Status::CREDIT;

        $this->assertTrue($transaction->isCredit());
    }

    public function test_is_credit_returns_false_for_debit_type(): void
    {
        $transaction = new Transaction();
        $transaction->trx_type = Status::DEBIT;

        $this->assertFalse($transaction->isCredit());
    }

    public function test_fillable_contains_expected_fields(): void
    {
        $transaction = new Transaction();
        $fillable = $transaction->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('order_id', $fillable);
        $this->assertContains('deposit_id', $fillable);
        $this->assertContains('amount', $fillable);
        $this->assertContains('payment_method', $fillable);
        $this->assertContains('transaction_id', $fillable);
        $this->assertContains('sender_number', $fillable);
        $this->assertContains('trx_type', $fillable);
        $this->assertContains('remarks', $fillable);
    }
}
