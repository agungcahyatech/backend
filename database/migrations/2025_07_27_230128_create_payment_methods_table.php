<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('image_path')->nullable(); // Logo metode pembayaran
            $table->string('name'); // Nama tampilan, e.g., "QRIS", "BCA Virtual Account"
            $table->string('provider'); // Provider gateway, e.g., "tokopay", "duitku"
            $table->string('code'); // Kode unik dari provider, e.g., "QRIS", "BCAVA"
            $table->string('group'); // Grup untuk frontend, e.g., "E-Wallet", "Virtual Account"
            $table->string('type'); // Tipe pembayaran, e.g., "e-wallet", "va"
            $table->decimal('fee_flat', 15, 2)->default(0); // Biaya flat
            $table->decimal('fee_percent', 5, 2)->default(0); // Biaya persen
            $table->unsignedBigInteger('min_amount')->default(0); // Nominal minimum
            $table->unsignedBigInteger('max_amount')->default(0); // Nominal maksimum
            $table->boolean('is_active')->default(true); // Status aktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};