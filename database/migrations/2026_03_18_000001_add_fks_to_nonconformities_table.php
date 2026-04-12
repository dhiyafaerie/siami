<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nonconformities', function (Blueprint $table) {
            $table->foreignId('standards_id')->nullable()->constrained('standards')->nullOnDelete();
            $table->foreignId('prodis_id')->nullable()->constrained('prodis')->nullOnDelete();
            $table->foreignId('auditors_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('nonconformities', function (Blueprint $table) {
            $table->dropForeign(['standards_id']);
            $table->dropForeign(['prodis_id']);
            $table->dropForeign(['auditors_id']);
            $table->dropColumn(['standards_id', 'prodis_id', 'auditors_id', 'description']);
        });
    }
};
