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
        Schema::create('prodiattachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standards_id')->constrained();
            $table->foreignId('prodis_id')->constrained();
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->text('keterangan');
            $table->text('link_bukti');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodiattachments');
    }
};
