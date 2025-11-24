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
        Schema::create('game_configuration_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_configuration_id')->constrained()->onDelete('cascade');
            $table->string('input_name'); // e.g., "user_id", "zone_id"
            $table->string('label'); // e.g., "User ID"
            $table->string('placeholder'); // e.g., "Masukkan User ID"
            $table->json('options')->nullable(); // Untuk select/dropdown (e.g., daftar server)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_configuration_fields');
    }
};
