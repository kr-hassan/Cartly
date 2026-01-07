<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        
        .company-info h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .company-info p {
            font-size: 11px;
            color: #666;
            margin: 2px 0;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-info h2 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .invoice-info p {
            font-size: 11px;
            color: #666;
            margin: 2px 0;
        }
        
        .invoice-body {
            margin-bottom: 30px;
        }
        
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .billing-box {
            flex: 1;
            margin-right: 20px;
        }
        
        .billing-box:last-child {
            margin-right: 0;
        }
        
        .billing-box h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .billing-box p {
            font-size: 11px;
            color: #666;
            margin: 3px 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table thead {
            background-color: #f5f5f5;
        }
        
        .items-table th {
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border-bottom: 2px solid #333;
        }
        
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        
        .items-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .summary-section {
            margin-top: 20px;
            margin-left: auto;
            width: 300px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11px;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .summary-row.discount {
            color: #d32f2f;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cfe2ff; color: #084298; }
        .status-shipped { background-color: #cff4fc; color: #055160; }
        .status-completed { background-color: #d1e7dd; color: #0f5132; }
        .status-cancelled { background-color: #f8d7da; color: #842029; }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .invoice-container {
                max-width: 100%;
            }
            
            @page {
                margin: 1cm;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <h1>{{ config('app.name', 'Cartly') }}</h1>
                <p>E-Commerce Store</p>
                <p>Email: info@cartly.com</p>
                <p>Phone: +1 (555) 123-4567</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ $order->order_number }}</p>
                <p><strong>Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-{{ $order->status }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </p>
            </div>
        </div>
        
        <div class="invoice-body">
            <div class="billing-section">
                <div class="billing-box">
                    <h3>Bill To</h3>
                    <p><strong>{{ $order->customer_name }}</strong></p>
                    <p>{{ $order->shipping_address }}</p>
                    @if($order->shipping_city)
                        <p>{{ $order->shipping_city }}{{ $order->shipping_postal_code ? ', ' . $order->shipping_postal_code : '' }}</p>
                    @endif
                    @if($order->customer_phone)
                        <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                    @endif
                    @if($order->customer_email)
                        <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                    @endif
                </div>
                <div class="billing-box">
                    <h3>Shipping To</h3>
                    <p><strong>{{ $order->customer_name }}</strong></p>
                    <p>{{ $order->shipping_address }}</p>
                    @if($order->shipping_city)
                        <p>{{ $order->shipping_city }}{{ $order->shipping_postal_code ? ', ' . $order->shipping_postal_code : '' }}</p>
                    @endif
                </div>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ $currency->formatAmount($item->price) }}</td>
                            <td class="text-right">{{ $currency->formatAmount($item->total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="summary-section">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>{{ $currency->formatAmount($order->subtotal) }}</span>
                </div>
                @if($order->discount > 0)
                    <div class="summary-row discount">
                        <span>Discount:</span>
                        <span>-{{ $currency->formatAmount($order->discount) }}</span>
                    </div>
                @endif
                @if($order->shipping_cost > 0)
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>{{ $currency->formatAmount($order->shipping_cost) }}</span>
                    </div>
                @endif
                @if($order->tax > 0)
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>{{ $currency->formatAmount($order->tax) }}</span>
                    </div>
                @endif
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>{{ $currency->formatAmount($order->total) }}</span>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>

