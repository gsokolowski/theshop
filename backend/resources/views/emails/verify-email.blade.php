<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
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
        .button:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
        }
        .expiration-notice {
            color: #666;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verify Your Email Address</h1>
        </div>
        
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            
            <p>Thank you for registering with us! Please verify your email address by clicking the button below:</p>
            
            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
            </div>
            
            <p class="expiration-notice">
                <strong>Note:</strong> This verification link will expire in 48 hours.
            </p>
            
            <p>If the button doesn't work, you can copy and paste the following link into your browser:</p>
            <p style="word-break: break-all; color: #007bff;">{{ $verificationUrl }}</p>
            
            <p>If you didn't create an account, please ignore this email.</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} The Shop. All rights reserved.</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
