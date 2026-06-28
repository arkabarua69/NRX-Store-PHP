<?php

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
        Schema::create('auto_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_id')->constrained('variations')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('order_id')->nullable();
            $table->string('code');
            $table->boolean('status')->default(Status::AVAILABLE);
            $table->string('transaction_id', 55)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_vouchers');
    }
};
