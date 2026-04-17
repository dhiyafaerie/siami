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
        Schema::table('auditscores', function (Blueprint $table) {
            $table->unsignedTinyInteger('keyword_index')->nullable()->after('prodis_id');
        });
    }

    public function down(): void
    {
        Schema::table('auditscores', function (Blueprint $table) {
            $table->dropColumn('keyword_index');
        });
    }
};
