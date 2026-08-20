<!DOCTYPE html>
<html>
<head>
    <title>Verify Your may22sms Account</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 40px 20px;">
    <div style="max-w-md; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center;">
        <h1 style="color: #0d9488; margin-top: 0; font-size: 28px;">may22sms</h1>
        
        <p style="color: #374151; font-size: 16px; margin-bottom: 24px;">Hello <strong>{{ $user->name }}</strong>,</p>
        
        <p style="color: #4b5563; font-size: 15px; margin-bottom: 30px;">Thank you for registering! Please use the following One-Time Password (OTP) to verify your email address and activate your account:</p>
        
        <div style="margin: 30px 0;">
            <span style="font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #000000; background-color: #facc15; padding: 15px 30px; border-radius: 8px; display: inline-block;">{{ $otp }}</span>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">This code will expire shortly. If you did not request this verification, please ignore this email.</p>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="color: #9ca3af; font-size: 12px; margin: 0;">&copy; {{ date('Y') }} may22sms. All rights reserved.</p>
    </div>
</body>
</html>