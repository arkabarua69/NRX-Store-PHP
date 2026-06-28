<?php

use App\Constants\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->enum('type', [Status::INGAME, Status::TOPUP, Status::VOUCHER]);
            $table->integer('percentage')->nullable()->default(0);
            $table->integer('order_column')->default(0);
            $table->boolean('status')->default(Status::ACTIVE);
            $table->integer('uid_checker')->default(0);
            $table->boolean('has_tutorial')->default(false);
            $table->string('tutorial_link', 1024)->nullable();
            $table->string('tutorial_text', 1024)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
