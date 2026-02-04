<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();

            $table->foreignId('surat_jalan_id')
                  ->constrained('surat_jalan')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('action', 100); // CREATE / EDIT / SCAN / UPLOAD
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['surat_jalan_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
