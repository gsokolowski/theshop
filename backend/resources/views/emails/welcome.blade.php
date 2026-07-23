<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
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
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
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
            <h1>Welcome to The Shop</h1>
        </div>

        <div class="content">
            <p>Hello {{ $user->name }},</p>

            @if ($source === 'google')
                <p>Thanks for signing in with Google. Your account is ready—here’s a quick welcome from us.</p>
            @else
                <p>Your email is verified and your account is all set. We’re glad you’re here.</p>
            @endif

            <p>Browse our products, complete your profile anytime, and reach out if you need anything.</p>

            <div style="text-align: center;">
                <a href="{{ $shopUrl }}" class="button" style="color: #ffffff;">Visit The Shop</a>
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} The Shop. All rights reserved.</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
