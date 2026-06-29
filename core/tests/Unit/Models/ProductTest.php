<?php

namespace Tests\Unit\Models;

use App\Constants\Status;
use App\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_is_voucher_returns_true_for_voucher_type(): void
    {
        $product = new Product();
        $product->type = Status::VOUCHER;

        $this->assertTrue($product->isVoucher());
    }

    public function test_is_voucher_returns_false_for_topup_type(): void
    {
        $product = new Product();
        $product->type = Status::TOPUP;

        $this->assertFalse($product->isVoucher());
    }

    public function test_is_in_game_returns_true_for_ingame_type(): void
    {
        $product = new Product();
        $product->type = Status::INGAME;

        $this->assertTrue($product->isInGame());
    }

    public function test_is_in_game_returns_false_for_topup_type(): void
    {
        $product = new Product();
        $product->type = Status::TOPUP;

        $this->assertFalse($product->isInGame());
    }

    public function test_is_topup_returns_true_for_topup_type(): void
    {
        $product = new Product();
        $product->type = Status::TOPUP;

        $this->assertTrue($product->isTopup());
    }

    public function test_is_topup_returns_false_for_voucher_type(): void
    {
        $product = new Product();
        $product->type = Status::VOUCHER;

        $this->assertFalse($product->isTopup());
    }

    public function test_fillable_contains_expected_fields(): void
    {
        $product = new Product();
        $fillable = $product->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('content', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('percentage', $fillable);
        $this->assertContains('uid_checker', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('has_tutorial', $fillable);
        $this->assertContains('tutorial_link', $fillable);
        $this->assertContains('tutorial_text', $fillable);
    }

    public function test_casts_contains_expected_fields(): void
    {
        $product = new Product();
        $casts = $product->getCasts();

        $this->assertEquals('boolean', $casts['status']);
        $this->assertEquals('boolean', $casts['has_tutorial']);
        $this->assertEquals('integer', $casts['uid_checker']);
    }

    public function test_placeholder_image_path_constant(): void
    {
        $this->assertEquals(
            'assets/template/images/placeholder.jpeg',
            Product::PLACEHOLDER_IMAGE_PATH
        );
    }
}
