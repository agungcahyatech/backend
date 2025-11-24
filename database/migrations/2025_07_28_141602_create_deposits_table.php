<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_id')->unique();
            $table->string('payment_method_name'); // e.g., "BCA Virtual Account"
            $table->decimal('amount', 15, 2); // Jumlah yang diminta user
            $table->decimal('final_amount', 15, 2); // Jumlah yang harus dibayar (termasuk fee)
            $table->enum('status', ['pending', 'success', 'failed', 'approved'])->default('pending');
            $table->text('payment_url')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('deposits'); }
};