<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prodiattachments', function (Blueprint $table) {
            $table->dropForeign(['standards_id']);
            $table->dropForeign(['prodis_id']);

            $table->foreign('standards_id')->references('id')->on('standards')->onDelete('cascade');
            $table->foreign('prodis_id')->references('id')->on('prodis')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('prodiattachments', function (Blueprint $table) {
            $table->dropForeign(['standards_id']);
            $table->dropForeign(['prodis_id']);

            $table->foreign('standards_id')->references('id')->on('standards');
            $table->foreign('prodis_id')->references('id')->on('prodis');
        });
    }
};
