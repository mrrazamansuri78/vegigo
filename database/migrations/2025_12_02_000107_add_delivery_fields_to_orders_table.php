<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_boy_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('pickup_address')->nullable()->after('customer_name');
            $table->string('drop_address')->nullable()->after('pickup_address');
            $table->decimal('distance_km', 8, 2)->nullable()->after('drop_address');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_boy_id']);
            $table->dropColumn(['delivery_boy_id', 'pickup_address', 'drop_address', 'distance_km']);
        });
    }
};

