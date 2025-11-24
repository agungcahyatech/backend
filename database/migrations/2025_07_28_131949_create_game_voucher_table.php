<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('game_voucher', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
            $table->primary(['game_id', 'voucher_id']); // Primary key gabungan
        });
    }
    public function down(): void { Schema::dropIfExists('game_voucher'); }
};