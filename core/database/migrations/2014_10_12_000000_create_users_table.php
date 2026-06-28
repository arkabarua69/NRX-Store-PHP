<?php

use App\Constants\Role;
use App\Constants\Status;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->enum('role', [Role::USER, Role::ADMIN])->default(Role::USER);
            $table->decimal('balance', 16, 2)->default(0.00);
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('gauth_id')->nullable();
            $table->rememberToken();
            $table->boolean('is_reseller')->default(Status::INACTIVE);
            $table->boolean('status')->default(Status::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
