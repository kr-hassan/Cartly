<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingSetting;
use Illuminate\Http\Request;

class ShippingSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shippingSettings = cache()->remember('admin.shipping_settings', 300, function () {
            return ShippingSetting::ordered()->get();
        });
        return view('admin.shipping-settings.index', compact('shippingSettings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.shipping-settings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'district_name' => 'required|string|max:255',
            'division' => 'nullable|string|max:255',
            'shipping_cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        ShippingSetting::create([
            'district_name' => $validated['district_name'],
            'division' => $validated['division'] ?? null,
            'shipping_cost' => $validated['shipping_cost'],
            'is_active' => $request->has('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        // Clear cache
        cache()->forget('admin.shipping_settings');
        cache()->forget('shipping_settings.active');

        return redirect()->route('admin.shipping-settings.index')
            ->with('success', 'Shipping setting created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShippingSetting $shippingSetting)
    {
        return view('admin.shipping-settings.edit', compact('shippingSetting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShippingSetting $shippingSetting)
    {
        $validated = $request->validate([
            'district_name' => 'required|string|max:255',
            'division' => 'nullable|string|max:255',
            'shipping_cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $shippingSetting->update([
            'district_name' => $validated['district_name'],
            'division' => $validated['division'] ?? null,
            'shipping_cost' => $validated['shipping_cost'],
            'is_active' => $request->has('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        // Clear cache
        cache()->forget('admin.shipping_settings');
        cache()->forget('shipping_settings.active');

        return redirect()->route('admin.shipping-settings.index')
            ->with('success', 'Shipping setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShippingSetting $shippingSetting)
    {
        $shippingSetting->delete();

        // Clear cache
        cache()->forget('admin.shipping_settings');
        cache()->forget('shipping_settings.active');

        return redirect()->route('admin.shipping-settings.index')
            ->with('success', 'Shipping setting deleted successfully.');
    }
}
