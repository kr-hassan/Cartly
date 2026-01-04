<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use DB facade to add indexes safely
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS orders_status_created_idx ON orders(status, created_at)');
        } catch (\Exception $e) {
            // Index might already exist or table structure doesn't support it
        }
        
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS orders_payment_created_idx ON orders(payment_status, created_at)');
        } catch (\Exception $e) {
            // Index might already exist
        }
        
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS orders_user_created_idx ON orders(user_id, created_at)');
        } catch (\Exception $e) {
            // Index might already exist
        }

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS carts_user_created_idx ON carts(user_id, created_at)');
        } catch (\Exception $e) {
            // Index might already exist
        }
        
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS carts_session_created_idx ON carts(session_id, created_at)');
        } catch (\Exception $e) {
            // Index might already exist
        }

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS order_items_order_product_idx ON order_items(order_id, product_id)');
        } catch (\Exception $e) {
            // Index might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            try {
                $table->dropIndex('orders_status_created_idx');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('orders_payment_created_idx');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('orders_user_created_idx');
            } catch (\Exception $e) {}
        });

        Schema::table('carts', function (Blueprint $table) {
            try {
                $table->dropIndex('carts_user_created_idx');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('carts_session_created_idx');
            } catch (\Exception $e) {}
        });

        Schema::table('order_items', function (Blueprint $table) {
            try {
                $table->dropIndex('order_items_order_product_idx');
            } catch (\Exception $e) {}
        });
    }
};
