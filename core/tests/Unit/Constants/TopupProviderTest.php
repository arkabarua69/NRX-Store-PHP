<?php

namespace Tests\Unit\Constants;

use App\Constants\TopupProvider;
use Tests\TestCase;

class TopupProviderTest extends TestCase
{
    public function test_provider_constants(): void
    {
        $this->assertEquals('FreeFire', TopupProvider::FREEFIRE);
        $this->assertEquals('Unipin', TopupProvider::UNIPIN);
    }

    public function test_options_maps_constants_to_labels(): void
    {
        $expected = [
            'FreeFire' => 'Free Fire',
            'Unipin' => 'Unipin',
        ];

        $this->assertEquals($expected, TopupProvider::OPTIONS);
    }

    public function test_product_variations_contains_expected_items(): void
    {
        $this->assertCount(10, TopupProvider::PRODUCTVARIATIONS);
        $this->assertEquals('25 Diamond', TopupProvider::PRODUCTVARIATIONS['0']);
        $this->assertEquals('Level Up Pass', TopupProvider::PRODUCTVARIATIONS['9']);
    }
}
