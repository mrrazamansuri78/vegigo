<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('delivery_boy_profiles', function (Blueprint $table) {
            // Profile info
            $table->string('vehicle_id')->nullable()->after('user_id');
            $table->string('role_title')->nullable()->after('vehicle_id'); // e.g., "Lead Delivery Partner"
            $table->boolean('is_verified')->default(false)->after('role_title');
            
            // Shifts & preferences
            $table->string('shift_start_time')->nullable()->after('is_on_route'); // e.g., "06:00"
            $table->string('shift_end_time')->nullable()->after('shift_start_time'); // e.g., "14:00"
            $table->string('vehicle_type')->nullable()->after('shift_end_time'); // e.g., "EV two-wheeler"
            $table->string('preferred_zone')->nullable()->after('vehicle_type'); // e.g., "Central Bengaluru"
            
            // Settings
            $table->boolean('auto_accept_urgent_jobs')->default(false)->after('preferred_zone');
            $table->boolean('share_live_location')->default(false)->after('auto_accept_urgent_jobs');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_boy_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_id',
                'role_title',
                'is_verified',
                'shift_start_time',
                'shift_end_time',
                'vehicle_type',
                'preferred_zone',
                'auto_accept_urgent_jobs',
                'share_live_location',
            ]);
        });
    }
};

