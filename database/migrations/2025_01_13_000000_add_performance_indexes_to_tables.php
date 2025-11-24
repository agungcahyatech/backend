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
        // Add indexes for better query performance with IF NOT EXISTS logic
        
        // Products table indexes - hanya yang belum ada
        if (!$this->indexExists('products', 'products_game_id_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('game_id');
            });
        }
        
        if (!$this->indexExists('products', 'products_product_category_id_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('product_category_id');
            });
        }
        
        if (!$this->indexExists('products', 'products_is_active_game_id_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['is_active', 'game_id']);
            });
        }
        
        if (!$this->indexExists('products', 'products_provider_provider_sku_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['provider', 'provider_sku']);
            });
        }

        // Games table indexes
        if (!$this->indexExists('games', 'games_category_id_index')) {
            Schema::table('games', function (Blueprint $table) {
                $table->index('category_id');
            });
        }
        
        if (!$this->indexExists('games', 'games_is_popular_index')) {
            Schema::table('games', function (Blueprint $table) {
                $table->index('is_popular');
            });
        }
        
        if (!$this->indexExists('games', 'games_is_active_category_id_index')) {
            Schema::table('games', function (Blueprint $table) {
                $table->index(['is_active', 'category_id']);
            });
        }

        // Transactions table indexes
        if (!$this->indexExists('transactions', 'transactions_status_index')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('status');
            });
        }
        
        if (!$this->indexExists('transactions', 'transactions_user_id_index')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('user_id');
            });
        }
        
        if (!$this->indexExists('transactions', 'transactions_product_id_index')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('product_id');
            });
        }
        
        if (!$this->indexExists('transactions', 'transactions_created_at_index')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('created_at');
            });
        }

        // Categories table indexes
        if (!$this->indexExists('categories', 'categories_display_order_index')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->index('display_order');
            });
        }

        // Deposits table indexes
        if (!$this->indexExists('deposits', 'deposits_status_index')) {
            Schema::table('deposits', function (Blueprint $table) {
                $table->index('status');
            });
        }
        
        if (!$this->indexExists('deposits', 'deposits_expired_at_index')) {
            Schema::table('deposits', function (Blueprint $table) {
                $table->index('expired_at');
            });
        }

        // Deposit vouchers table indexes
        if (!$this->indexExists('deposit_vouchers', 'deposit_vouchers_expired_at_index')) {
            Schema::table('deposit_vouchers', function (Blueprint $table) {
                $table->index('expired_at');
            });
        }

        // Articles table indexes
        if (!$this->indexExists('articles', 'articles_created_at_index')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->index('created_at');
            });
        }

        // Sliders table indexes
        if (!$this->indexExists('sliders', 'sliders_display_order_index')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->index('display_order');
            });
        }
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes - simplified approach
        $this->dropIndexIfExists('sliders', 'sliders_display_order_index');
        $this->dropIndexIfExists('articles', 'articles_created_at_index');
        $this->dropIndexIfExists('deposit_vouchers', 'deposit_vouchers_expired_at_index');
        $this->dropIndexIfExists('deposits', 'deposits_expired_at_index');
        $this->dropIndexIfExists('deposits', 'deposits_status_index');
        $this->dropIndexIfExists('categories', 'categories_display_order_index');
        $this->dropIndexIfExists('transactions', 'transactions_created_at_index');
        $this->dropIndexIfExists('transactions', 'transactions_product_id_index');
        $this->dropIndexIfExists('transactions', 'transactions_user_id_index');
        $this->dropIndexIfExists('transactions', 'transactions_status_index');
        $this->dropIndexIfExists('games', 'games_is_active_category_id_index');
        $this->dropIndexIfExists('games', 'games_is_popular_index');
        $this->dropIndexIfExists('games', 'games_category_id_index');
        $this->dropIndexIfExists('products', 'products_provider_provider_sku_index');
        $this->dropIndexIfExists('products', 'products_is_active_game_id_index');
        $this->dropIndexIfExists('products', 'products_product_category_id_index');
        $this->dropIndexIfExists('products', 'products_game_id_index');
    }

    /**
     * Drop index if exists
     */
    private function dropIndexIfExists(string $table, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            \DB::statement("DROP INDEX {$index} ON {$table}");
        }
    }
};