<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 10);
            $table->enum('side', ['buy', 'sell']);
            $table->decimal('price', 32, 8);
            $table->decimal('amount', 32, 16);
            $table->tinyInteger('status')->default(1); // 1=open,2=filled,3=cancelled
            $table->decimal('locked_value', 32, 16)->default(0);
            $table->timestamps();
            $table->index(['symbol', 'side', 'price', 'status']);
        });
    }   


    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
