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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "100 Diamonds"
            $table->string('icon_path')->nullable(); // Ikon produk
            $table->text('description')->nullable();
            $table->decimal('base_price', 15, 2)->default(0); // Harga modal/dasar
            $table->string('provider_sku'); // SKU dari provider, e.g., "ML100"
            $table->string('provider'); // Nama provider, e.g., "digiflazz"
            $table->foreignId('product_category_id')->constrained('product_categories')->onDelete('cascade');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
