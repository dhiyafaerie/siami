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
        Schema::create('auditscores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standards_id')->constrained()->onDelete('cascade');
            $table->foreignId('auditors_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prodis_id')->constrained()->onDelete('cascade');
            $table->integer('score')->unsigned(); // 1-4
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditscores');
    }
};
