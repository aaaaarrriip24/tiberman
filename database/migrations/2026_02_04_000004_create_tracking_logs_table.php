<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tracking_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('surat_jalan_id')
                  ->constrained('surat_jalan')
                  ->cascadeOnDelete();

            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);

            $table->string('ip_address', 50)->nullable();

            $table->foreignId('scan_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('scanned_at')->useCurrent();

            $table->timestamps();

            $table->index(['surat_jalan_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_logs');
    }
};
