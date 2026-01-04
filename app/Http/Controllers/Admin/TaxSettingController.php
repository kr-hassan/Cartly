<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxSetting;
use Illuminate\Http\Request;

class TaxSettingController extends Controller
{
    /**
     * Show the form for editing tax settings.
     */
    public function edit()
    {
        $taxSetting = TaxSetting::getOrCreateSetting();
        return view('admin.tax-settings.edit', compact('taxSetting'));
    }

    /**
     * Update tax settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $taxSetting = TaxSetting::getOrCreateSetting();
        $taxSetting->update([
            'tax_rate' => $validated['tax_rate'],
            'free_shipping_threshold' => $validated['free_shipping_threshold'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        // Clear cache if using any tax-related cache
        cache()->forget('tax_settings.active');
        cache()->forget('tax_rate');
        cache()->forget('free_shipping_threshold');

        return redirect()->route('admin.tax-settings.edit')
            ->with('success', 'Tax settings updated successfully.');
    }
}
