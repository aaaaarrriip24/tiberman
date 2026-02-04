<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['superuser', 'admin', 'creator', 'view_only'])
                  ->default('creator')
                  ->after('password');

            $table->foreignId('group_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('groups')
                  ->nullOnDelete();

            $table->index(['role', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'group_id']);
            $table->dropConstrainedForeignId('group_id');
            $table->dropColumn('role');
        });
    }
};
