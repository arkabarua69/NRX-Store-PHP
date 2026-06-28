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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('order_id')->nullable();
            $table->foreignId('deposit_id')->nullable();
            $table->decimal('amount', 16, 2)->default(0.00);
            $table->string('payment_method', 55);
            $table->string('transaction_id', 55)->unique();
            $table->enum('trx_type', [Status::DEBIT, Status::CREDIT])->default(Status::CREDIT);
            $table->text('remarks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function(Blueprint $table){
            $table->dropForeign(['user_id']);
            $table->dropIfExists();
        });
    }
};
