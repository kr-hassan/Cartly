<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    protected $fillable = [
        'tax_rate',
        'free_shipping_threshold',
        'is_active',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the current tax rate (as decimal, e.g., 0.10 for 10%).
     */
    public static function getTaxRate(): float
    {
        $setting = self::where('is_active', true)->first();
        
        if ($setting) {
            return (float) ($setting->tax_rate / 100); // Convert percentage to decimal
        }

        return 0.10; // Default 10% if no setting found
    }

    /**
     * Get the current tax rate (as percentage, e.g., 10.00 for 10%).
     */
    public static function getTaxRatePercentage(): float
    {
        $setting = self::where('is_active', true)->first();
        
        if ($setting) {
            return (float) $setting->tax_rate;
        }

        return 10.00; // Default 10% if no setting found
    }

    /**
     * Get or create the single tax setting.
     */
    public static function getOrCreateSetting(): self
    {
        return self::firstOrCreate([], [
            'tax_rate' => 10.00,
            'free_shipping_threshold' => null,
            'is_active' => true,
        ]);
    }

    /**
     * Get free shipping threshold.
     */
    public static function getFreeShippingThreshold(): ?float
    {
        $setting = self::where('is_active', true)->first();
        
        if ($setting && $setting->free_shipping_threshold !== null) {
            return (float) $setting->free_shipping_threshold;
        }

        return null; // No free shipping threshold
    }

    /**
     * Check if order qualifies for free shipping.
     */
    public static function qualifiesForFreeShipping(float $orderAmount): bool
    {
        $threshold = self::getFreeShippingThreshold();
        
        if ($threshold === null) {
            return false;
        }

        return $orderAmount >= $threshold;
    }
}
