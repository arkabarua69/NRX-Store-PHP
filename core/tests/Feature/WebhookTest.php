<?php

namespace Tests\Feature;

use App\Constants\OrderStatus;
use App\Models\AutoVoucher;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Variation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_handles_successful_topup(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['type' => 'topup']);
        $variation = Variation::factory()->create(['product_id' => $product->id]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'track_id' => 'TRACK123',
            'status' => OrderStatus::AUTOPROCESSING,
        ]);

        $autoVoucher = AutoVoucher::factory()->create([
            'order_id' => $order->id,
            'variation_id' => $variation->id,
            'status' => 0,
        ]);

        $response = $this->postJson('/api/auto-topup/webhook', [
            'track_id' => 'TRACK123',
            'status' => 'success',
            'message' => 'Topup completed',
            'uid' => 'UID12345',
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(OrderStatus::COMPLETED, $order->status);
        $this->assertEquals('Topup completed successfully via webhook.', $order->delivery_message);
    }

    public function test_webhook_handles_failed_topup(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['type' => 'topup']);
        $variation = Variation::factory()->create(['product_id' => $product->id]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'track_id' => 'TRACK456',
            'status' => OrderStatus::AUTOPROCESSING,
        ]);

        $autoVoucher = AutoVoucher::factory()->create([
            'order_id' => $order->id,
            'variation_id' => $variation->id,
            'status' => 0,
        ]);

        $response = $this->postJson('/api/auto-topup/webhook', [
            'track_id' => 'TRACK456',
            'status' => 'failed',
            'message' => 'Invalid player ID',
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(OrderStatus::PROCESSING, $order->status);
        $this->assertNull($order->voucher_code);

        $autoVoucher->refresh();
        $this->assertEquals(1, $autoVoucher->status);
    }

    public function test_webhook_returns_404_for_unknown_order(): void
    {
        $response = $this->postJson('/api/auto-topup/webhook', [
            'track_id' => 'UNKNOWN_TRACK',
            'status' => 'success',
        ]);

        $response->assertStatus(404);
    }
}
