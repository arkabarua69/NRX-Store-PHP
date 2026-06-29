<?php

namespace Tests\Unit\Constants;

use App\Constants\DepositStatus;
use Tests\TestCase;

class DepositStatusTest extends TestCase
{
    public function test_constants_have_expected_values(): void
    {
        $this->assertEquals('paid', DepositStatus::PAID);
        $this->assertEquals('unpaid', DepositStatus::UNPAID);
    }

    public function test_color_returns_success_for_paid(): void
    {
        $this->assertEquals('text-success', DepositStatus::color(DepositStatus::PAID));
    }

    public function test_color_returns_danger_for_unpaid(): void
    {
        $this->assertEquals('text-danger', DepositStatus::color(DepositStatus::UNPAID));
    }
}
