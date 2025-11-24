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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('developer')->nullable();
            $table->string('brand')->nullable();
            $table->string('allowed_region')->nullable();
            $table->string('image_thumbnail_path'); // Gambar ikon/thumbnail
            $table->string('image_banner_path');  // Gambar banner
            $table->text('description'); // Deskripsi singkat
            $table->longText('long_description'); // Deskripsi panjang
            $table->json('faq')->nullable(); // Untuk menyimpan tanya jawab
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('game_configuration_id')->nullable()->constrained('game_configurations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
