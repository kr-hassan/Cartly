<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Coupon;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;
use App\Models\ShippingSetting;
use App\Models\TaxSetting;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected OrderService $orderService;
    protected PaymentService $paymentService;

    public function __construct(
        CartService $cartService,
        OrderService $orderService,
        PaymentService $paymentService
    ) {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Show checkout form.
     */
    public function index()
    {
        $cartTotals = $this->cartService->getCartTotals(auth()->user());
        
        if ($cartTotals['item_count'] === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $user = auth()->user() ?? null;
        
        // Get active shipping settings for dropdown
        $shippingSettings = cache()->remember('shipping_settings.active', 3600, function () {
            return ShippingSetting::active()->ordered()->get();
        });
        
        // Get tax rate for frontend
        $taxRate = cache()->remember('tax_rate', 3600, function () {
            return TaxSetting::getTaxRatePercentage();
        });
        
        // Get free shipping threshold
        $freeShippingThreshold = cache()->remember('free_shipping_threshold', 3600, function () {
            return TaxSetting::getFreeShippingThreshold();
        });
        
        return view('checkout.index', compact('cartTotals', 'user', 'shippingSettings', 'taxRate', 'freeShippingThreshold'));
    }

    /**
     * Process checkout.
     */
    public function store(CheckoutRequest $request)
    {
        $cartTotals = $this->cartService->getCartTotals(auth()->user());
        
        if ($cartTotals['item_count'] === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        try {
            // Create order
            $order = $this->orderService->createOrder($request->validated(), auth()->user());

            // Process payment
            $paymentResult = $this->paymentService->processPayment($order);

            if (!$paymentResult['success']) {
                return redirect()->back()
                    ->with('error', $paymentResult['message'] ?? 'Payment processing failed.');
            }

            // Update order payment status if needed
            if (isset($paymentResult['payment_status'])) {
                $order->update(['payment_status' => $paymentResult['payment_status']]);
            }

            // Load order with relationships for email
            $order->load(['items', 'items.product']);

            // Send order confirmation email
            try {
                $email = $order->customer_email;
                if ($email) {
                    Mail::to($email)->send(new OrderConfirmation($order));
                }
            } catch (\Exception $e) {
                // Log error but don't fail the order
                \Log::error('Failed to send order confirmation email: ' . $e->getMessage());
            }

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Order placed successfully! An email confirmation has been sent to your email address.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Calculate shipping cost (AJAX).
     */
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'district' => 'required|string',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $subtotal = $request->subtotal ?? 0;
        $discount = $request->discount ?? 0;
        $orderAmount = $subtotal - $discount;

        // Check if order qualifies for free shipping
        $freeShipping = TaxSetting::qualifiesForFreeShipping($orderAmount);
        
        if ($freeShipping) {
            $shippingCost = 0;
        } else {
            $shippingCost = ShippingSetting::getShippingCost($request->district);
        }

        return response()->json([
            'success' => true,
            'shipping_cost' => (float) $shippingCost,
            'shipping_cost_formatted' => number_format($shippingCost, 2),
            'free_shipping' => $freeShipping,
            'free_shipping_threshold' => TaxSetting::getFreeShippingThreshold(),
        ]);
    }

    /**
     * Validate coupon code (AJAX).
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon || !$coupon->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired coupon code.',
            ]);
        }

        $cartTotals = $this->cartService->getCartTotals(auth()->user());
        $discount = $coupon->calculateDiscount($cartTotals['subtotal']);

        return response()->json([
            'valid' => true,
            'discount' => (float) $discount,
            'discount_formatted' => number_format($discount, 2),
            'coupon' => [
                'code' => $coupon->code,
                'name' => $coupon->name,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
        ]);
    }
}
