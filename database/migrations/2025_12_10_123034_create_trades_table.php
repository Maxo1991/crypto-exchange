<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buy_order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('sell_order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('symbol', 10);
            $table->decimal('price', 32, 8);
            $table->decimal('amount', 32, 16);
            $table->decimal('usd_volume', 32, 16);
            $table->decimal('fee_usd', 32, 16);
            $table->timestamps();
        });
    }


    public function down(): void {
        Schema::dropIfExists('trades');
    }
};
