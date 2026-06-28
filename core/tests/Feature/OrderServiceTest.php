<?php

namespace Tests\Feature;

use App\Constants\OrderStatus;
use App\Constants\Status;
use App\Models\AutoVoucher;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Variation;
use App\Models\Voucher;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private OrderService $orderService;
    private User $user;
    private Product $topupProduct;
    private Product $voucherProduct;
    private Variation $topupVariation;
    private Variation $voucherVariation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
        $this->user = User::factory()->create(['balance' => 1000]);

        $this->topupProduct = Product::factory()->create(['type' => Status::TOPUP]);
        $this->topupVariation = Variation::factory()->create([
            'product_id' => $this->topupProduct->id,
            'price' => 100,
            'buy_rate' => 80,
            'stock' => 10,
        ]);

        $this->voucherProduct = Product::factory()->create(['type' => Status::VOUCHER]);
        $this->voucherVariation = Variation::factory()->create([
            'product_id' => $this->voucherProduct->id,
            'price' => 50,
            'buy_rate' => 40,
            'stock' => 5,
        ]);

        Voucher::factory()->count(3)->create([
            'variation_id' => $this->voucherVariation->id,
            'status' => Status::AVAILABLE,
        ]);
    }

    public function test_it_can_create_an_order(): void
    {
        $this->actingAs($this->user);

        $request = new \Illuminate\Http\Request([
            'variation_id' => $this->topupVariation->id,
            'quantity' => 1,
            'payment_method' => 'uddoktapay',
            'account_info' => ['player_id' => '123456789'],
        ]);

        $response = $this->orderService->addOrder($request);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'variation_id' => $this->topupVariation->id,
            'amount' => 100,
            'status' => OrderStatus::PENDING,
        ]);
    }

    public function test_it_creates_order_with_wallet_payment(): void
    {
        $this->actingAs($this->user);

        $request = new \Illuminate\Http\Request([
            'variation_id' => $this->topupVariation->id,
            'quantity' => 1,
            'payment_method' => Status::WALLET,
            'account_info' => ['player_id' => '123456789'],
        ]);

        $this->orderService->addOrder($request);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(OrderStatus::PROCESSING, $order->status);

        $this->user->refresh();
        $this->assertEquals(900, $this->user->balance);
    }

    public function test_it_throws_error_for_insufficient_balance(): void
    {
        $poorUser = User::factory()->create(['balance' => 10]);
        $this->actingAs($poorUser);

        $request = new \Illuminate\Http\Request([
            'variation_id' => $this->topupVariation->id,
            'quantity' => 1,
            'payment_method' => Status::WALLET,
            'account_info' => ['player_id' => '123456789'],
        ]);

        $response = $this->orderService->addOrder($request);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_it_completes_voucher_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->voucherProduct->id,
            'variation_id' => $this->voucherVariation->id,
            'amount' => 50,
            'quantity' => 1,
            'status' => OrderStatus::PENDING,
        ]);

        $this->orderService->completeOrder($order, 'uddoktapay', 'TXN' . strRandom());

        $order->refresh();
        $this->assertEquals(OrderStatus::COMPLETED, $order->status);
        $this->assertNotNull($order->voucher_code);
    }

    public function test_it_handles_reseller_bonus(): void
    {
        $reseller = User::factory()->create(['balance' => 1000, 'is_reseller' => true]);
        $this->topupProduct->update(['percentage' => 10]);

        $this->actingAs($reseller);

        $request = new \Illuminate\Http\Request([
            'variation_id' => $this->topupVariation->id,
            'quantity' => 1,
            'payment_method' => Status::WALLET,
            'account_info' => ['player_id' => '123456789'],
        ]);

        $this->orderService->addOrder($request);

        $reseller->refresh();
        $this->assertEquals(910, $reseller->balance);
    }

    public function test_it_cancels_order_and_restores_voucher(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->voucherProduct->id,
            'variation_id' => $this->voucherVariation->id,
            'amount' => 50,
            'quantity' => 1,
            'status' => OrderStatus::PROCESSING,
        ]);

        $voucher = Voucher::where('variation_id', $this->voucherVariation->id)
            ->where('status', Status::AVAILABLE)->first();
        $voucher->update(['status' => Status::SOLD, 'order_id' => $order->id]);

        OrderService::cancelOrder($order);

        $order->refresh();
        $this->assertEquals(Status::CANCELLED, $order->status);

        $voucher->refresh();
        $this->assertEquals(Status::AVAILABLE, $voucher->status);
    }

    public function test_transaction_is_unique(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->topupProduct->id,
            'variation_id' => $this->topupVariation->id,
            'amount' => 100,
            'status' => OrderStatus::PENDING,
        ]);

        Transaction::factory()->create(['transaction_id' => 'DUPLICATE_TXN']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction ID already exists.');

        $this->orderService->completeOrder($order, 'uddoktapay', 'DUPLICATE_TXN');
    }

    public function test_auto_topup_dispatches_for_automatic_variations(): void
    {
        $autoVariation = Variation::factory()->create([
            'product_id' => $this->topupProduct->id,
            'price' => 200,
            'buy_rate' => 160,
            'stock' => 10,
            'automatic' => true,
            'provider' => 'FreeFire',
        ]);

        AutoVoucher::factory()->create([
            'variation_id' => $autoVariation->id,
            'status' => Status::AVAILABLE,
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->topupProduct->id,
            'variation_id' => $autoVariation->id,
            'amount' => 200,
            'status' => OrderStatus::PENDING,
        ]);

        $this->orderService->completeOrder($order, 'uddoktapay', 'TXN_AUTO_' . strRandom());

        $order->refresh();
        $this->assertEquals(OrderStatus::AUTOPROCESSING, $order->status);
    }
}
