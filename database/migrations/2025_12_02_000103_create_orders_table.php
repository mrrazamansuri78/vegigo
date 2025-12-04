<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // farmer owner
            $table->string('order_code')->unique();
            $table->string('customer_name');
            $table->json('items'); // [{product:"Potato", quantity:"20 kg"}]
            $table->enum('status', ['pending', 'ready', 'picked_up', 'delivered'])->default('pending');
            $table->date('ready_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};


