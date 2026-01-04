<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    protected $fillable = [
        'district_name',
        'division',
        'shipping_cost',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to get only active shipping settings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('district_name', 'asc');
    }

    /**
     * Get shipping cost for a district.
     */
    public static function getShippingCost(string $district): float
    {
        $setting = self::active()
            ->where('district_name', 'like', "%{$district}%")
            ->first();

        if ($setting) {
            return (float) $setting->shipping_cost;
        }

        // Default to "Outside Dhaka" if district not found
        $default = self::active()
            ->where('district_name', 'like', '%Outside%')
            ->orWhere('district_name', 'like', '%outside%')
            ->first();

        return $default ? (float) $default->shipping_cost : 60.00;
    }
}
