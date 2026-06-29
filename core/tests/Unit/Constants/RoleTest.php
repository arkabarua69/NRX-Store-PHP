<?php

namespace Tests\Unit\Constants;

use App\Constants\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    public function test_user_role_constant(): void
    {
        $this->assertEquals('user', Role::USER);
    }

    public function test_admin_role_constant(): void
    {
        $this->assertEquals('admin', Role::ADMIN);
    }
}
