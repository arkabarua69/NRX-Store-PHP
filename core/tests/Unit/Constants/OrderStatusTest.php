<?php

namespace Tests\Unit\Constants;

use App\Constants\OrderStatus;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_constants_have_expected_values(): void
    {
        $this->assertEquals('pending', OrderStatus::PENDING);
        $this->assertEquals('cancelled', OrderStatus::CANCELLED);
        $this->assertEquals('completed', OrderStatus::COMPLETED);
        $this->assertEquals('processing', OrderStatus::PROCESSING);
        $this->assertEquals('auto-processing', OrderStatus::AUTOPROCESSING);
        $this->assertEquals('hold', OrderStatus::HOLD);
    }

    public function test_orderlist_contains_all_statuses(): void
    {
        $expected = [
            OrderStatus::COMPLETED,
            OrderStatus::PROCESSING,
            OrderStatus::AUTOPROCESSING,
            OrderStatus::HOLD,
            OrderStatus::PENDING,
            OrderStatus::CANCELLED,
        ];

        $this->assertEquals($expected, OrderStatus::ORDERLIST);
    }

    public function test_color_returns_correct_classes(): void
    {
        $this->assertEquals('text-success', OrderStatus::color(OrderStatus::COMPLETED));
        $this->assertEquals('text-primary', OrderStatus::color(OrderStatus::PROCESSING));
        $this->assertEquals('text-info', OrderStatus::color(OrderStatus::AUTOPROCESSING));
        $this->assertEquals('text-warning', OrderStatus::color(OrderStatus::HOLD));
        $this->assertEquals('text-warning', OrderStatus::color(OrderStatus::PENDING));
        $this->assertEquals('text-danger', OrderStatus::color(OrderStatus::CANCELLED));
    }

    public function test_admin_color_returns_correct_classes(): void
    {
        $this->assertEquals('success', OrderStatus::adminColor(OrderStatus::COMPLETED));
        $this->assertEquals('info', OrderStatus::adminColor(OrderStatus::PROCESSING));
        $this->assertEquals('gray', OrderStatus::adminColor(OrderStatus::AUTOPROCESSING));
        $this->assertEquals('warning', OrderStatus::adminColor(OrderStatus::HOLD));
        $this->assertEquals('warning', OrderStatus::adminColor(OrderStatus::PENDING));
        $this->assertEquals('danger', OrderStatus::adminColor(OrderStatus::CANCELLED));
    }

    public function test_orderlist_count_matches_statuses(): void
    {
        $this->assertCount(6, OrderStatus::ORDERLIST);
    }

    public function test_color_and_admin_color_cover_all_orderlist_statuses(): void
    {
        foreach (OrderStatus::ORDERLIST as $status) {
            $this->assertNotEmpty(OrderStatus::color($status));
            $this->assertNotEmpty(OrderStatus::adminColor($status));
        }
    }
}
