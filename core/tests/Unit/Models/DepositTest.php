<?php

namespace Tests\Unit\Models;

use App\Constants\Status;
use App\Models\Deposit;
use Tests\TestCase;

class DepositTest extends TestCase
{
    public function test_is_unpaid_returns_true_when_status_unpaid(): void
    {
        $deposit = new Deposit();
        $deposit->status = Status::UNPAID;

        $this->assertTrue($deposit->isUnpaid());
    }

    public function test_is_unpaid_returns_false_when_status_paid(): void
    {
        $deposit = new Deposit();
        $deposit->status = Status::PAID;

        $this->assertFalse($deposit->isUnpaid());
    }

    public function test_fillable_contains_expected_fields(): void
    {
        $deposit = new Deposit();
        $fillable = $deposit->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('amount', $fillable);
        $this->assertContains('track_id', $fillable);
        $this->assertContains('status', $fillable);
    }
}
