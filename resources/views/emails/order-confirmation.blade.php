<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #2563eb;">Order Confirmation</h1>
        
        <p>Thank you for your order!</p>
        
        <div style="background: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h2 style="margin-top: 0;">Order Details</h2>
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('F d, Y h:i A') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            @if($order->customer_name)
                <p><strong>Customer:</strong> {{ $order->customer_name }}</p>
            @endif
            @if($order->customer_email)
                <p><strong>Email:</strong> {{ $order->customer_email }}</p>
            @endif
            <p><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>
        </div>
        
        @if($order->shipping_address)
        <div style="background: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h2 style="margin-top: 0;">Shipping Address</h2>
            <p>{{ $order->customer_name }}</p>
            <p>{{ $order->shipping_address }}</p>
            @if($order->shipping_city)
                <p>{{ $order->shipping_city }}{{ $order->shipping_postal_code ? ', ' . $order->shipping_postal_code : '' }}</p>
            @endif
            @if($order->shipping_country)
                <p>{{ $order->shipping_country }}</p>
            @endif
            @if($order->customer_phone)
                <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
            @endif
        </div>
        @endif

        <h2>Order Items</h2>
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background: #f3f4f6;">
                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Product</th>
                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Quantity</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{ $item->product_name }}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{ $item->quantity }}</td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">${{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="padding: 10px; text-align: right; font-weight: bold;">Total:</td>
                    <td style="padding: 10px; text-align: right; font-weight: bold;">${{ number_format($order->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <p>We'll keep you updated on your order status.</p>
        
        <p>Thank you for shopping with us!</p>
    </div>
</body>
</html>

