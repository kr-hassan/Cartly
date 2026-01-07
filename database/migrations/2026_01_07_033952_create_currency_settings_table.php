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
        Schema::create('currency_settings', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3)->default('USD'); // USD, BDT, etc.
            $table->string('currency_symbol', 10)->default('$'); // $, ৳, etc.
            $table->string('currency_name', 50)->default('US Dollar'); // US Dollar, Bangladeshi Taka, etc.
            $table->string('currency_position', 10)->default('before'); // before or after
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_settings');
    }
};
