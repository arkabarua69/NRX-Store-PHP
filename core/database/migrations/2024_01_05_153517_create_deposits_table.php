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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->decimal('amount', 16, 2)->default(0.00);
            $table->string('track_id', 25);
            $table->enum('status', [Status::UNPAID, Status::PAID])->default(Status::UNPAID);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function(Blueprint $table){
            $table->dropForeign(['user_id']);
            $table->dropIfExists();
        });
    }
};
