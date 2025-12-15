<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('pickup_latitude', 10, 8)->nullable()->after('pickup_address');
            $table->decimal('pickup_longitude', 11, 8)->nullable()->after('pickup_latitude');
            $table->decimal('drop_latitude', 10, 8)->nullable()->after('drop_address');
            $table->decimal('drop_longitude', 11, 8)->nullable()->after('drop_latitude');
            $table->decimal('total_amount', 10, 2)->nullable()->after('distance_km');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_latitude',
                'pickup_longitude',
                'drop_latitude',
                'drop_longitude',
                'total_amount',
            ]);
        });
    }
};

