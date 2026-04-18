<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('target_role', ['auditor', 'prodi']);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->foreignId('cycles_id')->nullable()->constrained('cycles')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['target_role', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
