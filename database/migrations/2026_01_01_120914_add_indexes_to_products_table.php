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
        Schema::table('products', function (Blueprint $table) {
            // Add composite index for common queries
            $table->index(['is_active', 'stock_quantity'], 'products_active_stock_idx');
            $table->index(['is_active', 'created_at'], 'products_active_created_idx');
            $table->index(['price', 'discount_price'], 'products_price_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_stock_idx');
            $table->dropIndex('products_active_created_idx');
            $table->dropIndex('products_price_idx');
        });
    }
};
