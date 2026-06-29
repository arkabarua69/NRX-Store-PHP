<?php

namespace Tests\Unit\Models;

use App\Constants\Status;
use App\Models\Order;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_is_pending_returns_true_when_status_is_pending(): void
    {
        $order = new Order();
        $order->status = Status::PENDING;

        $this->assertTrue($order->isPending());
    }

    public function test_is_pending_returns_false_when_status_is_completed(): void
    {
        $order = new Order();
        $order->status = Status::COMPLETED;

        $this->assertFalse($order->isPending());
    }

    public function test_is_pending_returns_false_when_status_is_processing(): void
    {
        $order = new Order();
        $order->status = Status::PROCESSING;

        $this->assertFalse($order->isPending());
    }

    public function test_is_pending_returns_false_when_status_is_cancelled(): void
    {
        $order = new Order();
        $order->status = Status::CANCELLED;

        $this->assertFalse($order->isPending());
    }

    public function test_fillable_contains_expected_fields(): void
    {
        $order = new Order();
        $fillable = $order->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('variation_id', $fillable);
        $this->assertContains('amount', $fillable);
        $this->assertContains('profit', $fillable);
        $this->assertContains('delivery_message', $fillable);
        $this->assertContains('account_info', $fillable);
        $this->assertContains('provider_data', $fillable);
        $this->assertContains('voucher_code', $fillable);
        $this->assertContains('track_id', $fillable);
        $this->assertContains('quantity', $fillable);
        $this->assertContains('attempts', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_casts_contains_expected_fields(): void
    {
        $order = new Order();
        $casts = $order->getCasts();

        $this->assertEquals('integer', $casts['attempts']);
        $this->assertEquals('array', $casts['account_info']);
        $this->assertEquals('array', $casts['provider_data']);
    }
}
