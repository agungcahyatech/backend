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
        Schema::table('game_configuration_fields', function (Blueprint $table) {
            $table->string('placeholder')->nullable()->change();
            $table->string('validation_rules')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_configuration_fields', function (Blueprint $table) {
            $table->string('placeholder')->nullable(false)->change();
            $table->string('validation_rules')->nullable(false)->change();
        });
    }
};
