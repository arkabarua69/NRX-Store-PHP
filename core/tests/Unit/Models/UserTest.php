<?php

namespace Tests\Unit\Models;

use App\Constants\Role;
use App\Constants\Status;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $user = new User();
        $user->role = Role::ADMIN;

        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_user_role(): void
    {
        $user = new User();
        $user->role = Role::USER;

        $this->assertFalse($user->isAdmin());
    }

    public function test_is_user_returns_true_for_user_role(): void
    {
        $user = new User();
        $user->role = Role::USER;

        $this->assertTrue($user->isUser());
    }

    public function test_is_user_returns_false_for_admin_role(): void
    {
        $user = new User();
        $user->role = Role::ADMIN;

        $this->assertFalse($user->isUser());
    }

    public function test_is_banned_returns_true_when_status_inactive(): void
    {
        $user = new User();
        $user->status = Status::INACTIVE;

        $this->assertTrue($user->isBanned());
    }

    public function test_is_banned_returns_false_when_status_active(): void
    {
        $user = new User();
        $user->status = Status::ACTIVE;

        $this->assertFalse($user->isBanned());
    }

    public function test_is_reseller_returns_true_when_active(): void
    {
        $user = new User();
        $user->is_reseller = Status::ACTIVE;

        $this->assertTrue($user->isReseller());
    }

    public function test_is_reseller_returns_false_when_inactive(): void
    {
        $user = new User();
        $user->is_reseller = Status::INACTIVE;

        $this->assertFalse($user->isReseller());
    }

    public function test_get_redirect_route_returns_admin_for_admin(): void
    {
        $user = new User();
        $user->role = Role::ADMIN;

        $this->assertEquals('/admin', $user->getRedirectRoute());
    }

    public function test_get_redirect_route_returns_home_for_user(): void
    {
        $user = new User();
        $user->role = Role::USER;

        $this->assertEquals('/', $user->getRedirectRoute());
    }

    public function test_fillable_contains_expected_fields(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('gauth_id', $fillable);
    }

    public function test_hidden_contains_sensitive_fields(): void
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }
}
