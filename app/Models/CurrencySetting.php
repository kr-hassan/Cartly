<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencySetting extends Model
{
    protected $fillable = [
        'currency_code',
        'currency_symbol',
        'currency_name',
        'currency_position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get or create the single currency setting.
     */
    public static function getOrCreateSetting(): self
    {
        return self::firstOrCreate([], [
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_name' => 'US Dollar',
            'currency_position' => 'before',
            'is_active' => true,
        ]);
    }

    /**
     * Get the active currency setting.
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Get the currency symbol.
     */
    public static function getSymbol(): string
    {
        $setting = self::getActive();
        return $setting ? $setting->currency_symbol : '$';
    }

    /**
     * Get the currency code.
     */
    public static function getCode(): string
    {
        $setting = self::getActive();
        return $setting ? $setting->currency_code : 'USD';
    }

    /**
     * Get the currency name.
     */
    public static function getName(): string
    {
        $setting = self::getActive();
        return $setting ? $setting->currency_name : 'US Dollar';
    }

    /**
     * Get the currency position.
     */
    public static function getPosition(): string
    {
        $setting = self::getActive();
        return $setting ? $setting->currency_position : 'before';
    }

    /**
     * Format amount with currency symbol.
     */
    public static function format(float $amount, int $decimals = 2): string
    {
        $symbol = self::getSymbol();
        $position = self::getPosition();
        $formatted = number_format($amount, $decimals);
        
        if ($position === 'before') {
            return $symbol . $formatted;
        } else {
            return $formatted . ' ' . $symbol;
        }
    }

    /**
     * Format amount with currency symbol (instance method).
     */
    public function formatAmount(float $amount, int $decimals = 2): string
    {
        $formatted = number_format($amount, $decimals);
        
        if ($this->currency_position === 'before') {
            return $this->currency_symbol . $formatted;
        } else {
            return $formatted . ' ' . $this->currency_symbol;
        }
    }
}
