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
        Schema::create('prodis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculties_id')->constrained('faculties')->onDelete('cascade');
            $table->string('programstudi');
            $table->enum('jenjang', ['sarjana', 'magister']);
            $table->string('nidn')->unique();
            $table->string('nik_nip')->unique();
            $table->string('telpon', 255);
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodis');
    }
};
