<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('surat_jalan', function (Blueprint $table) {
            $table->id();

            $table->string('kode_surat_jalan', 100)->unique();
            $table->string('qr_code_path', 255)->nullable();

            $table->enum('status', ['created', 'on_delivery', 'delivered'])
                  ->default('created');

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_jalan');
    }
};
