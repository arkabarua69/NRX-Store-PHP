<?php

namespace Tests\Unit\Helpers;

use App\Constants\Status;
use Tests\TestCase;

class FunctionsTest extends TestCase
{
    public function test_amount_formats_integer(): void
    {
        $this->assertEquals('100', amount(100));
    }

    public function test_amount_formats_float_with_decimals(): void
    {
        $this->assertEquals('99.99', amount(99.99, 2));
    }

    public function test_amount_strips_commas(): void
    {
        $this->assertEquals('1000', amount('1,000'));
    }

    public function test_amount_handles_string_with_decimals(): void
    {
        $this->assertEquals('1234.56', amount('1,234.56', 2));
    }

    public function test_amount_handles_zero(): void
    {
        $this->assertEquals('0', amount(0));
    }

    public function test_amount_handles_negative(): void
    {
        $this->assertEquals('-50', amount(-50));
    }

    public function test_str_random_default_length(): void
    {
        $result = strRandom();
        $this->assertEquals(12, strlen($result));
    }

    public function test_str_random_custom_length(): void
    {
        $result = strRandom(20);
        $this->assertEquals(20, strlen($result));
    }

    public function test_str_random_contains_only_allowed_chars(): void
    {
        $result = strRandom(100);
        $allowed = 'ABCDEFGHJKMNOPQRSTUVWXYZ123456789';
        for ($i = 0; $i < strlen($result); $i++) {
            $this->assertStringContainsString($result[$i], $allowed);
        }
    }

    public function test_str_random_generates_unique_values(): void
    {
        $results = [];
        for ($i = 0; $i < 50; $i++) {
            $results[] = strRandom();
        }
        // Very unlikely to have duplicates in 50 random 12-char strings
        $this->assertCount(50, array_unique($results));
    }

    public function test_slug_generates_url_friendly_string(): void
    {
        $this->assertEquals('hello-world', slug('Hello World'));
    }

    public function test_slug_handles_special_characters(): void
    {
        $this->assertEquals('test-product-2024', slug('Test Product 2024!'));
    }

    public function test_slug_handles_empty_string(): void
    {
        $this->assertEquals('', slug(''));
    }

    public function test_product_type_returns_topup(): void
    {
        $this->assertEquals('Game / Topup', productType(Status::TOPUP));
    }

    public function test_product_type_returns_ingame(): void
    {
        $this->assertEquals('Game / In Game', productType(Status::INGAME));
    }

    public function test_product_type_returns_voucher(): void
    {
        $this->assertEquals('Game / Voucher', productType(Status::VOUCHER));
    }

    public function test_product_type_returns_digital_product_for_unknown(): void
    {
        $this->assertEquals('Digital Product', productType('unknown'));
    }

    public function test_json_to_plain_text_converts_json(): void
    {
        $json = json_encode(['player_id' => '12345', 'server_name' => 'Asia']);
        $result = jsonToPlainText($json);
        $this->assertStringContainsString('Player Id: 12345', $result);
        $this->assertStringContainsString('Server Name: Asia', $result);
        $this->assertStringContainsString('<br>', $result);
    }

    public function test_json_to_plain_text_handles_invalid_json(): void
    {
        $this->assertEquals('', jsonToPlainText('not json'));
    }

    public function test_json_to_plain_text_handles_empty_json(): void
    {
        $this->assertEquals('', jsonToPlainText(json_encode([])));
    }

    public function test_json_to_plain_text_admin_converts_json(): void
    {
        $json = json_encode(['player_id' => '12345', 'server_name' => 'Asia']);
        $result = jsonToPlainTextAdmin($json);
        $this->assertStringContainsString('Player Id: 12345', $result);
        $this->assertStringContainsString('Server Name: Asia', $result);
        $this->assertStringContainsString(PHP_EOL, $result);
    }

    public function test_get_percentage_amount_basic(): void
    {
        $this->assertEquals('10', getPercentageAmount(100, 10));
    }

    public function test_get_percentage_amount_zero_percentage(): void
    {
        $this->assertEquals('0', getPercentageAmount(100, 0));
    }

    public function test_get_percentage_amount_full_percentage(): void
    {
        $this->assertEquals('200', getPercentageAmount(200, 100));
    }

    public function test_get_percentage_amount_decimal_result(): void
    {
        $this->assertEquals('33', getPercentageAmount(100, 33));
    }

    public function test_get_percentage_amount_large_values(): void
    {
        $this->assertEquals('5000', getPercentageAmount(10000, 50));
    }
}
