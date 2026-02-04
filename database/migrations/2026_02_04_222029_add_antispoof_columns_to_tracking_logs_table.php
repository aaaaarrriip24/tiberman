<?php

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
        Schema::table('tracking_logs', function (Blueprint $table) {
            $table->decimal('ip_latitude', 10, 7)->nullable()->after('ip_address');
            $table->decimal('ip_longitude', 10, 7)->nullable()->after('ip_latitude');
            $table->decimal('distance_ip_km', 10, 2)->nullable()->after('ip_longitude');

            $table->boolean('is_suspicious')->default(false)->after('distance_ip_km');
            $table->string('suspicious_reason', 255)->nullable()->after('is_suspicious');

            $table->text('user_agent')->nullable()->after('suspicious_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_logs', function (Blueprint $table) {
            //
        });
    }
};
