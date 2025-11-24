<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul artikel
            $table->string('slug')->unique(); // URL unik untuk artikel
            $table->string('image_path'); // Path ke gambar utama
            $table->longText('content'); // Konten artikel
            $table->boolean('is_published')->default(false); // Status publish
            $table->timestamp('publish_date')->nullable(); // Tanggal publish (bisa untuk scheduling)
            $table->unsignedBigInteger('view_count')->default(0); // Jumlah view
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};