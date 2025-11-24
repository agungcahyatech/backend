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
            $table->string('type')->default('text')->after('placeholder');
            $table->string('validation_rules')->nullable()->after('type');
            $table->boolean('is_required')->default(true)->after('validation_rules');
            $table->integer('display_order')->default(0)->after('is_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_configuration_fields', function (Blueprint $table) {
            $table->dropColumn(['type', 'validation_rules', 'is_required', 'display_order']);
        });
    }
};
