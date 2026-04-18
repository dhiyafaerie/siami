<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditscores', function (Blueprint $table) {
            $table->index(['prodis_id', 'standards_id'], 'auditscores_prodi_standard_idx');
        });

        Schema::table('prodiattachments', function (Blueprint $table) {
            $table->index(['prodis_id', 'standards_id'], 'prodiattachments_prodi_standard_idx');
        });

        Schema::table('nonconformities', function (Blueprint $table) {
            $table->index(['prodis_id', 'standards_id'], 'nonconformities_prodi_standard_idx');
        });
    }

    public function down(): void
    {
        Schema::table('auditscores', function (Blueprint $table) {
            $table->dropIndex('auditscores_prodi_standard_idx');
        });

        Schema::table('prodiattachments', function (Blueprint $table) {
            $table->dropIndex('prodiattachments_prodi_standard_idx');
        });

        Schema::table('nonconformities', function (Blueprint $table) {
            $table->dropIndex('nonconformities_prodi_standard_idx');
        });
    }
};
