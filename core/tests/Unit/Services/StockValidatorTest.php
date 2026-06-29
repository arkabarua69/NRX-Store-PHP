<?php

namespace Tests\Unit\Services;

use App\Constants\Status;
use App\Models\AutoVoucher;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Variation;
use App\Services\TopupProvider\Validators\StockValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockValidatorTest extends TestCase
{
    use RefreshDatabase;

    private StockValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new StockValidator();
    }

    private function createOrderWithVariation(array $variationAttrs = []): Order
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variation = Variation::factory()->create(array_merge(
            ['product_id' => $product->id],
            $variationAttrs
        ));

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'amount' => 100,
            'track_id' => strRandom(),
            'status' => Status::PENDING,
        ]);

        $order->setRelation('variation', $variation);

        return $order;
    }

    public function test_returns_false_when_no_auto_vouchers_exist(): void
    {
        $order = $this->createOrderWithVariation(['stock' => 10, 'provider' => null]);

        $result = $this->validator->validateBeforePlacement($order);

        $this->assertFalse($result);
    }

    public function test_returns_false_when_stock_is_zero(): void
    {
        $order = $this->createOrderWithVariation(['stock' => 0, 'provider' => null]);

        AutoVoucher::create([
            'variation_id' => $order->variation->id,
            'code' => ['TEST123'],
            'status' => 1,
        ]);

        $result = $this->validator->validateBeforePlacement($order);

        $this->assertFalse($result);
    }

    public function test_returns_true_when_stock_and_auto_vouchers_available(): void
    {
        $order = $this->createOrderWithVariation(['stock' => 10, 'provider' => null]);

        AutoVoucher::create([
            'variation_id' => $order->variation->id,
            'code' => ['TEST123'],
            'status' => 1,
        ]);

        $result = $this->validator->validateBeforePlacement($order);

        $this->assertTrue($result);
    }

    public function test_falls_back_to_local_check_when_no_provider(): void
    {
        $order = $this->createOrderWithVariation(['stock' => 10, 'provider' => null]);

        AutoVoucher::create([
            'variation_id' => $order->variation->id,
            'code' => ['TEST123'],
            'status' => 1,
        ]);

        $result = $this->validator->validateBeforePlacement($order);

        $this->assertTrue($result);
    }

    public function test_unipin_provider_delegates_to_local_stock_check(): void
    {
        $order = $this->createOrderWithVariation(['stock' => 10, 'provider' => 'Unipin']);

        AutoVoucher::create([
            'variation_id' => $order->variation->id,
            'code' => ['TEST123'],
            'status' => 1,
        ]);

        $result = $this->validator->validateBeforePlacement($order);

        $this->assertTrue($result);
    }

    public function test_returns_false_when_auto_voucher_is_sold(): void
    {
        $order = $this->createOrderWithVariation(['stock' => 10, 'provider' => null]);

        AutoVoucher::create([
            'variation_id' => $order->variation->id,
            'code' => ['TEST123'],
            'status' => 0, // SOLD
        ]);

        $result = $this->validator->validateBeforePlacement($order);

        $this->assertFalse($result);
    }

    public function test_freefire_falls_back_to_local_when_no_api_config(): void
    {
        $order = $this->createOrderWithVariation(['stock' => 10, 'provider' => 'FreeFire']);

        AutoVoucher::create([
            'variation_id' => $order->variation->id,
            'code' => ['TEST123'],
            'status' => 1,
        ]);

        // gs() will have empty free_fire_server_url/api_key so it falls back
        $result = $this->validator->validateBeforePlacement($order);

        $this->assertTrue($result);
    }
}
