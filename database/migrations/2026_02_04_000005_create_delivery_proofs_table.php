<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_proofs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('surat_jalan_id')
                  ->constrained('surat_jalan')
                  ->cascadeOnDelete();

            // enforce 1:1 relationship with surat_jalan
            $table->unique('surat_jalan_id');

            $table->string('receiver_name', 150);
            $table->string('photo_path', 255)->nullable();
            $table->timestamp('received_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_proofs');
    }
};
