<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->string('invoice_id')->unique();
            $table->string('payment_method_name');
            $table->string('payment_method_code');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['UNPAID', 'PAID', 'EXPIRED', 'FAILED'])->default('UNPAID');
            $table->text('payment_url')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};