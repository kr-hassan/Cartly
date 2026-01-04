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
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('tax_rate', 5, 2)->default(10.00); // Tax rate in percentage (e.g., 10.00 for 10%)
            $table->decimal('free_shipping_threshold', 10, 2)->nullable(); // Free shipping threshold (e.g., 500.00)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default tax setting
        DB::table('tax_settings')->insert([
            'tax_rate' => 10.00,
            'free_shipping_threshold' => null, // No free shipping by default
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};
