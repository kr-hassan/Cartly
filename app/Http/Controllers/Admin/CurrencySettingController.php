<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurrencySetting;
use Illuminate\Http\Request;

class CurrencySettingController extends Controller
{
    /**
     * Show the form for editing currency settings.
     */
    public function edit()
    {
        $currencySetting = CurrencySetting::getOrCreateSetting();
        return view('admin.currency-settings.edit', compact('currencySetting'));
    }

    /**
     * Update currency settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'currency_code' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:10',
            'currency_name' => 'required|string|max:50',
            'currency_position' => 'required|in:before,after',
            'is_active' => 'boolean',
        ]);

        $currencySetting = CurrencySetting::getOrCreateSetting();
        $currencySetting->update([
            'currency_code' => strtoupper($validated['currency_code']),
            'currency_symbol' => $validated['currency_symbol'],
            'currency_name' => $validated['currency_name'],
            'currency_position' => $validated['currency_position'],
            'is_active' => $request->has('is_active'),
        ]);

        // Clear cache if using any currency-related cache
        cache()->forget('currency_settings.active');
        cache()->forget('currency_symbol');
        cache()->forget('currency_code');

        return redirect()->route('admin.currency-settings.edit')
            ->with('success', 'Currency settings updated successfully.');
    }
}
