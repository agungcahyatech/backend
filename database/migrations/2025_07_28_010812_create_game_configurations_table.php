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
        Schema::create('game_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama internal, e.g., "Mobile Legends Config"
            $table->text('guide_text')->nullable(); // Teks panduan
            $table->string('guide_image_path')->nullable(); // Gambar panduan
            $table->string('validation_provider')->nullable(); // Provider untuk validasi ID
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_configurations');
    }
};
