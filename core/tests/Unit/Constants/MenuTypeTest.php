<?php

namespace Tests\Unit\Constants;

use App\Constants\MenuType;
use Tests\TestCase;

class MenuTypeTest extends TestCase
{
    public function test_menu_type_constants(): void
    {
        $this->assertEquals('user', MenuType::USER);
        $this->assertEquals('guest', MenuType::GUEST);
        $this->assertEquals('both', MenuType::BOTH);
    }

    public function test_list_contains_all_types(): void
    {
        $expected = [MenuType::USER, MenuType::GUEST, MenuType::BOTH];
        $this->assertEquals($expected, MenuType::LIST);
    }
}
