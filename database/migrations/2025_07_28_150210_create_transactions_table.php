<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // Info Pembeli & Target
            $table->foreignId('user_id')->nullable()->constrained('users')->comment('User yang login di web kita');
            $table->string('game_user_id');
            $table->string('game_zone_id')->nullable();
            $table->string('nickname')->nullable()->comment('Nama nickname hasil validasi');

           // Info Produk & Harga
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null'); // Relasi ke produk
            $table->string('product_name');
            $table->string('provider_sku'); // <-- TAMBAHKAN INI
            $table->integer('quantity')->default(1);
            $table->decimal('base_price', 15, 2)->comment('Harga modal dari provider');

            // Info Provider & Status
            $table->string('provider_name')->nullable();
            $table->string('provider_order_id')->nullable()->comment('Invoice dari provider');
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'canceled'])->default('pending');
            $table->text('serial_number')->nullable()->comment('SN dari provider');
            $table->json('log')->nullable()->comment('Log histori dari transaksi');
            
            // Info Tambahan
            $table->string('transaction_type')->comment('Diambil dari nama Kategori Game');
            $table->string('ref_id')->nullable();
            $table->boolean('success_report_sent')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('transactions'); }
};