<?php

namespace Tests\Unit\Models;

use App\Models\Variation;
use Tests\TestCase;

class VariationTest extends TestCase
{
    public function test_is_automatic_returns_true_when_automatic(): void
    {
        $variation = new Variation();
        $variation->automatic = true;

        $this->assertTrue($variation->isAutomatic());
    }

    public function test_is_automatic_returns_false_when_not_automatic(): void
    {
        $variation = new Variation();
        $variation->automatic = false;

        $this->assertFalse($variation->isAutomatic());
    }

    public function test_fillable_contains_expected_fields(): void
    {
        $variation = new Variation();
        $fillable = $variation->getFillable();

        $this->assertContains('product_id', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('price', $fillable);
        $this->assertContains('buy_rate', $fillable);
        $this->assertContains('stock', $fillable);
        $this->assertContains('automatic', $fillable);
        $this->assertContains('provider', $fillable);
        $this->assertContains('provider_product_id', $fillable);
    }

    public function test_casts_contains_expected_fields(): void
    {
        $variation = new Variation();
        $casts = $variation->getCasts();

        $this->assertEquals('boolean', $casts['automatic']);
        $this->assertEquals('boolean', $casts['status']);
    }

    public function test_provider_type_throws_for_invalid_provider(): void
    {
        $variation = new Variation();
        $variation->provider = 'InvalidProvider';

        $order = new \App\Models\Order();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid top-up provider.');

        $variation->providerType($order);
    }
}
