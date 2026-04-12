<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nonconformities', function (Blueprint $table) {
            $table->enum('status', ['terbuka', 'dalam_perbaikan', 'ditutup'])->default('terbuka')->after('description');
            $table->text('tindakan_perbaikan')->nullable()->after('status');
            $table->date('deadline_perbaikan')->nullable()->after('tindakan_perbaikan');
            $table->timestamp('perbaikan_diajukan_at')->nullable()->after('deadline_perbaikan');
            $table->timestamp('verified_at')->nullable()->after('perbaikan_diajukan_at');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nonconformities', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'status', 'tindakan_perbaikan', 'deadline_perbaikan',
                'perbaikan_diajukan_at', 'verified_at', 'verified_by',
            ]);
        });
    }
};
