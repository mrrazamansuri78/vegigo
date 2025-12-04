<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add timeline timestamp fields
            $table->timestamp('accepted_at')->nullable()->after('ready_date');
            $table->timestamp('picked_up_at')->nullable()->after('accepted_at');
            $table->timestamp('delivered_at')->nullable()->after('picked_up_at');
            
            // Add location detail fields
            $table->string('landmark')->nullable()->after('drop_address');
            $table->string('drop_contact_person')->nullable()->after('landmark');
            $table->string('drop_contact_phone')->nullable()->after('drop_contact_person');
        });

        // Update status enum to include 'accepted'
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'ready', 'accepted', 'picked_up', 'delivered') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'accepted_at',
                'picked_up_at',
                'delivered_at',
                'landmark',
                'drop_contact_person',
                'drop_contact_phone',
            ]);
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'ready', 'picked_up', 'delivered') DEFAULT 'pending'");
    }
};

