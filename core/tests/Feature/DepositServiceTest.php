<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\User;
use App\Services\DepositService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositServiceTest extends TestCase
{
    use RefreshDatabase;

    private DepositService $depositService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->depositService = app(DepositService::class);
        $this->user = User::factory()->create(['balance' => 0]);
    }

    public function test_it_completes_deposit_and_credits_wallet(): void
    {
        $deposit = Deposit::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 500,
            'status' => 'unpaid',
        ]);

        $this->depositService->completeDeposit($deposit, 'uddoktapay', 'TXN_DEP_' . strRandom());

        $deposit->refresh();
        $this->assertEquals('paid', $deposit->status);

        $this->user->refresh();
        $this->assertEquals(500, $this->user->balance);
    }

    public function test_it_prevents_duplicate_transaction(): void
    {
        $deposit = Deposit::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 500,
            'status' => 'unpaid',
        ]);

        $this->depositService->completeDeposit($deposit, 'uddoktapay', 'TXN_DUP');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction ID already exists.');

        $this->depositService->completeDeposit($deposit, 'uddoktapay', 'TXN_DUP');
    }
}
