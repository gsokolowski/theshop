<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin: 0;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .order-block {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .order-block:last-child {
            margin-bottom: 0;
        }
        .product-row {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }
        .product-row:last-child {
            margin-bottom: 0;
        }
        .product-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .product-details {
            flex: 1;
        }
        .product-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .product-meta {
            color: #666;
            font-size: 14px;
            margin-bottom: 3px;
        }
        .order-total {
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Confirmation</h1>
        </div>

        <div class="content">
            <p>Hello {{ $user->name }},</p>

            <p>Thank you for your purchase! Here are your order details:</p>

            @foreach ($ordersData as $order)
            <div class="order-block">
                <p style="margin-top: 0; margin-bottom: 15px;"><strong>Order #{{ $order['id'] }}</strong> — {{ $order['created_at'] }}</p>

                @foreach ($order['products'] as $product)
                <div class="product-row">
                    @if ($product['thumbnail'])
                    <img src="{{ $product['thumbnail'] }}" alt="{{ $product['name'] }}" class="product-thumb">
                    @endif
                    <div class="product-details">
                        <div class="product-name">{{ $product['name'] }}</div>
                        <div class="product-meta">${{ number_format($product['price'], 2) }} × {{ $order['qty'] }}</div>
                        @if ($product['size_name'])
                        <div class="product-meta">Size: {{ $product['size_name'] }}</div>
                        @endif
                        @if ($product['color_name'])
                        <div class="product-meta">Color: {{ $product['color_name'] }}</div>
                        @endif
                    </div>
                </div>
                @endforeach

                @if ($order['coupon'])
                <div class="product-meta">Coupon: {{ $order['coupon']->name }} ({{ $order['coupon']->discount }}% off)</div>
                @endif

                <div class="order-total">Total: ${{ number_format($order['total'], 2) }}</div>
            </div>
            @endforeach
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} The Shop. All rights reserved.</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
