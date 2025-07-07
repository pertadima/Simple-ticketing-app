<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
    <style>
        body {
            background: #f6f8fb;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 420px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            padding: 32px 28px 28px 28px;
        }
        .logo {
            text-align: center;
            margin-bottom: 18px;
        }
        .logo img {
            width: 60px;
            height: 60px;
        }
        h2 {
            color: #2d3748;
            text-align: center;
            margin-bottom: 10px;
        }
        p {
            color: #4a5568;
            font-size: 16px;
            text-align: center;
            margin-bottom: 28px;
        }
        .otp-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 18px 0;
            text-align: center;
            font-size: 32px;
            letter-spacing: 8px;
            color: #2563eb;
            font-weight: bold;
            margin-bottom: 24px;
            border: 1px dashed #2563eb;
        }
        .footer {
            text-align: center;
            color: #a0aec0;
            font-size: 13px;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <!-- Optional: Place your logo here -->
            <!-- <img src="https://yourdomain.com/logo.png" alt="App Logo"> -->
        </div>
        <h2>Password Reset Request</h2>
        <p>Use the OTP code below to reset your password.<br>
        This code is valid for 10 minutes.</p>
        <div class="otp-box">
            {{ $otp }}
        </div>
        <p class="footer">If you did not request this, please ignore this email.</p>
    </div>
</body>
</html>