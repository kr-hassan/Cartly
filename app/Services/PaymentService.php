<?php

namespace App\Services;

use App\Models\Order;

/**
 * Abstract payment service.
 * Can be extended to integrate with Stripe, SSLCommerz, etc.
 */
class PaymentService
{
    /**
     * Process payment for an order.
     * 
     * @param Order $order
     * @param array $paymentData
     * @return array
     */
    public function processPayment(Order $order, array $paymentData = []): array
    {
        // For COD, payment is automatically pending
        if ($order->payment_method === 'cod') {
            return [
                'success' => true,
                'payment_status' => 'pending',
                'message' => 'Order placed successfully. Payment will be collected on delivery.',
            ];
        }

        // For online payments, this method should be overridden in child classes
        // Example: StripePaymentService, SSLCommerzPaymentService
        throw new \Exception('Online payment integration not implemented. Please use COD.');
    }

    /**
     * Verify payment for an order.
     * 
     * @param Order $order
     * @param array $verificationData
     * @return bool
     */
    public function verifyPayment(Order $order, array $verificationData = []): bool
    {
        // Implementation depends on payment gateway
        return false;
    }

    /**
     * Refund payment for an order.
     * 
     * @param Order $order
     * @param float|null $amount
     * @return array
     */
    public function refundPayment(Order $order, ?float $amount = null): array
    {
        // Implementation depends on payment gateway
        throw new \Exception('Refund functionality not implemented.');
    }
}

