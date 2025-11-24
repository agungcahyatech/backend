<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Kode voucher yang dimasukkan user
            $table->text('description');
            $table->enum('discount_type', ['percentage', 'flat'])->default('flat'); // Tipe diskon
            $table->decimal('discount_value', 15, 2); // Nilai diskon (nominal atau persen)
            $table->dateTime('start_date'); // Tanggal mulai berlaku
            $table->dateTime('end_date'); // Tanggal berakhir
            $table->decimal('min_purchase', 15, 2)->default(0); // Minimal pembelian
            $table->unsignedInteger('total_usage_limit')->default(1); // Batas total penggunaan
            $table->unsignedInteger('user_usage_limit')->default(1); // Batas penggunaan per user
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('vouchers'); }
};