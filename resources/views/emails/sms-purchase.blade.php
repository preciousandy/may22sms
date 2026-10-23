@extends('emails.layouts.default')

@section('title', 'Your SMS Number is Ready!')

@section('preheader', 'Your SMS verification order #' . $order->id . ' is active. View your incoming SMS in your dashboard.')

@section('badge')
<span style="display: inline-block; padding: 5px 12px; font-size: 11px; font-weight: 700; color: #0284c7; background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 9999px; letter-spacing: 0.3px; text-transform: uppercase;">
    📱 SMS Active
</span>
@endsection

@section('greeting')
Your SMS Number is Ready! 📱
@endsection

@section('content')
<p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 1.7;">
    Hi <strong>{{ $user->name }}</strong>, your SMS verification number has been provisioned and is ready to receive messages.
</p>

<!-- Clean Modern Order Summary Table -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin: 24px 0;" class="email-data-box">
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Order ID</td>
                    <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 700; font-family: 'SF Mono', monospace;">#{{ $order->id }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Country</td>
                    <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 700;">{{ $order->country_name ?? $order->country ?? 'Global / International' }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Service / Product</td>
                    <td align="right" style="font-size: 13px; color: #0d9488; font-weight: 700;">{{ ucfirst($order->product ?? 'SMS Verification') }}</td>
                </tr>
            </table>
        </td>
    </tr>
    @if(!empty($order->phone_number))
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Assigned Number</td>
                    <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 700; font-family: 'SF Mono', monospace;">{{ $order->phone_number }}</td>
                </tr>
            </table>
        </td>
    </tr>
    @endif
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Total Charged</td>
                    <td align="right" style="font-size: 14px; color: #0f172a; font-weight: 800;">${{ number_format($order->price ?? $order->total_amount ?? 0, 2) }} USD</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 14px 20px;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Order Date</td>
                    <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 600;">{{ $order->created_at ? $order->created_at->format('M d, Y • h:i A') : now()->format('M d, Y • h:i A') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Modern Guidance Callout -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f0fdfa; border-left: 4px solid #0d9488; border-radius: 10px; margin: 24px 0;">
    <tr>
        <td style="padding: 16px 18px;">
            <div style="font-size: 13px; font-weight: 700; color: #0f766e; margin-bottom: 4px;">
                💡 How to view your verification SMS
            </div>
            <div style="font-size: 13px; color: #115e59; line-height: 1.5;">
                Keep your dashboard orders tab open. Once the SMS is received from the provider, the verification code will display on your screen in real time. If no SMS arrives within the order timeout, your funds are automatically credited back.
            </div>
        </td>
    </tr>
</table>
@endsection

@section('button')
<!-- Modern Bulletproof CTA Button -->
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/orders?type=sms' }}" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="20%" strokecolor="#0d9488" fillcolor="#0d9488">
<w:anchorlock/>
<center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">View Live SMS Order &rarr;</center>
</v:roundrect>
<![endif]-->
<a href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/orders?type=sms' }}" target="_blank" class="cta-button" style="mso-hide:all; display: inline-block; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35); letter-spacing: 0.2px;">
    View Live SMS Order &rarr;
</a>
@endsection
