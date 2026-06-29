<?php

namespace Tests\Unit\Constants;

use App\Constants\Status;
use Tests\TestCase;

class StatusTest extends TestCase
{
    public function test_status_constants(): void
    {
        $this->assertEquals(1, Status::ACTIVE);
        $this->assertEquals(0, Status::INACTIVE);
        $this->assertEquals(1, Status::DEFAULT);
    }

    public function test_type_constants(): void
    {
        $this->assertEquals('once', Status::ONCE);
        $this->assertEquals('daily', Status::DAILY);
    }

    public function test_transaction_type_constants(): void
    {
        $this->assertEquals('-', Status::CREDIT);
        $this->assertEquals('+', Status::DEBIT);
    }

    public function test_invoice_status_constants(): void
    {
        $this->assertEquals('paid', Status::PAID);
        $this->assertEquals('unpaid', Status::UNPAID);
    }

    public function test_order_status_constants(): void
    {
        $this->assertEquals('pending', Status::PENDING);
        $this->assertEquals('cancelled', Status::CANCELLED);
        $this->assertEquals('completed', Status::COMPLETED);
        $this->assertEquals('processing', Status::PROCESSING);
        $this->assertEquals('auto-processing', Status::AUTOPROCESSING);
        $this->assertEquals('hold', Status::HOLD);
        $this->assertEquals('wallet', Status::WALLET);
    }

    public function test_product_type_constants(): void
    {
        $this->assertEquals('topup', Status::TOPUP);
        $this->assertEquals('ingame', Status::INGAME);
        $this->assertEquals('voucher', Status::VOUCHER);
    }

    public function test_voucher_status_constants(): void
    {
        $this->assertEquals(0, Status::SOLD);
        $this->assertEquals(1, Status::AVAILABLE);
        $this->assertEquals(1, Status::ISVOUCHER);
        $this->assertEquals(0, Status::NOTVOUCHER);
    }

    public function test_orderlist_contains_all_order_statuses(): void
    {
        $expected = [
            Status::COMPLETED,
            Status::PROCESSING,
            Status::AUTOPROCESSING,
            Status::HOLD,
            Status::PENDING,
            Status::CANCELLED,
        ];

        $this->assertEquals($expected, Status::ORDERLIST);
    }
}
