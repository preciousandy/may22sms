<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            padding: 40px 20px;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .email-header {
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .email-logo {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }
        .email-content {
            padding: 40px 30px;
        }
        .email-image {
            width: 100%;
            max-width: 300px;
            margin: 0 auto 30px;
            display: block;
            border-radius: 12px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 16px;
        }
        .email-body {
            color: #4b5563;
            font-size: 16px;
            margin-bottom: 24px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 24px 0;
        }
        .info-box {
            background-color: #f0fdfa;
            border-left: 4px solid #0d9488;
            padding: 20px;
            border-radius: 8px;
            margin: 24px 0;
        }
        .info-box-label {
            font-size: 12px;
            font-weight: 600;
            color: #0d9488;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .info-box-value {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-text {
            color: #9ca3af;
            font-size: 12px;
        }
        .footer-links {
            margin-top: 12px;
        }
        .footer-links a {
            color: #0d9488;
            text-decoration: none;
            font-size: 12px;
            margin: 0 8px;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="{{ asset('logo/logo-light.png') }}" alt="Logo" class="email-logo">
        </div>
        <div class="email-content">
            @if (isset($headerImage))
                <img src="{{ $headerImage }}" alt="Email Header" class="email-image">
            @endif

            <div class="greeting">@yield('greeting')</div>
            
            <div class="email-body">
                @yield('content')
            </div>

            @hasSection('button')
                <div style="text-align: center;">
                    @yield('button')
                </div>
            @endif

            @if (isset($infoBox))
                <div class="info-box">
                    <div class="info-box-label">{{ $infoBox['label'] }}</div>
                    <div class="info-box-value">{{ $infoBox['value'] }}</div>
                </div>
            @endif
        </div>
        <div class="email-footer">
            <p class="footer-text">&copy; {{ date('Y') }} may22sms. All rights reserved.</p>
            <p class="footer-text" style="margin-top: 8px;">123 Business Street, Tech City, TC 12345</p>
            <div class="footer-links">
                <a href="{{ url('/') }}">Visit Website</a>
                <a href="{{ url('/contact') }}">Contact Us</a>
            </div>
        </div>
    </div>
</body>
</html>
