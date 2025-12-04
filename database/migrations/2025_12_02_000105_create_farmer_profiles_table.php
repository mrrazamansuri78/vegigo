<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('farmer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('farm_name')->nullable();
            $table->string('location_city')->nullable();
            $table->string('location_state')->nullable();
            $table->decimal('farm_size_acres', 8, 2)->nullable();
            $table->string('primary_crop')->nullable();
            $table->string('storage')->nullable();
            $table->string('certifications')->nullable();
            $table->decimal('fulfillment_rate', 5, 2)->default(0); // percentage e.g. 98.00
            $table->decimal('average_rating', 3, 2)->default(0); // 0-5 scale
            $table->integer('repeat_partners')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_profiles');
    }
};


