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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul halaman, e.g., "Syarat dan Ketentuan"
            $table->string('slug')->unique(); // URL-friendly, e.g., "syarat-dan-ketentuan"
            $table->longText('content'); // Konten halaman, bisa sangat panjang
            $table->boolean('is_published')->default(false); // Status publish (draft/live)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
