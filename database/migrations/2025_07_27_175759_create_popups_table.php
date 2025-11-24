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
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul popup
            $table->string('image_path'); // Path ke file gambar
            $table->text('content'); // Konten/deskripsi popup
            $table->string('link_url')->nullable(); // Link tautan (opsional)
            $table->date('start_date'); // Tanggal mulai tampil
            $table->date('end_date')->nullable(); // Tanggal selesai tampil (opsional)
            $table->boolean('is_active')->default(true); // Status aktif manual
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
