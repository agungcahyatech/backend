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
        Schema::table('deposit_voucher_usages', function (Blueprint $table) {
            $table->foreignId('deposit_voucher_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposit_voucher_usages', function (Blueprint $table) {
            $table->dropForeign(['deposit_voucher_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['deposit_voucher_id', 'user_id']);
        });
    }
};
