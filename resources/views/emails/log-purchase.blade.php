@extends('emails.layouts.default')

@section('title', 'Your Log Purchase is Complete!')

@section('preheader', 'Your log order #' . $order->id . ' is ready. Access and download your credentials in your dashboard.')

@section('badge')
<span style="display: inline-block; padding: 5px 12px; font-size: 11px; font-weight: 700; color: #7c3aed; background-color: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 9999px; letter-spacing: 0.3px; text-transform: uppercase;">
    📄 Logs Ready
</span>
@endsection

@section('greeting')
Your Logs are Ready to Download! 📄
@endsection

@section('content')
<p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 1.7;">
    Hi <strong>{{ $user->name }}</strong>, your log purchase has been completed successfully! Your digital credentials are now securely available in your account.
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
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Items Delivered</td>
                    <td align="right" style="font-size: 13px; color: #0d9488; font-weight: 700;">
                        {{ $order->total_items ?? (isset($order->credentials) ? $order->credentials->count() : 1) }} item(s)
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Total Amount</td>
                    <td align="right" style="font-size: 14px; color: #0f172a; font-weight: 800;">${{ number_format($order->total_amount ?? 0, 2) }} USD</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Delivery Status</td>
                    <td align="right">
                        <span style="display: inline-block; background-color: #ede9fe; color: #6d28d9; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                            Instant Download
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 14px 20px;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Date &amp; Time</td>
                    <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 600;">{{ $order->created_at ? $order->created_at->format('M d, Y • h:i A') : now()->format('M d, Y • h:i A') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Security Tip Callout -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #faf5ff; border-left: 4px solid #8b5cf6; border-radius: 10px; margin: 24px 0;">
    <tr>
        <td style="padding: 16px 18px;">
            <div style="font-size: 13px; font-weight: 700; color: #6d28d9; margin-bottom: 4px;">
                🔐 Secure Encrypted Vault
            </div>
            <div style="font-size: 13px; color: #5b21b6; line-height: 1.5;">
                For your security, raw credentials and access keys are kept securely in your orders vault. Log in to copy or download your files at any time.
            </div>
        </td>
    </tr>
</table>
@endsection

@section('button')
<!-- Modern Bulletproof CTA Button -->
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/orders?type=logs' }}" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="20%" strokecolor="#0d9488" fillcolor="#0d9488">
<w:anchorlock/>
<center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">Access My Logs &rarr;</center>
</v:roundrect>
<![endif]-->
<a href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/orders?type=logs' }}" target="_blank" class="cta-button" style="mso-hide:all; display: inline-block; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35); letter-spacing: 0.2px;">
    Access My Logs &rarr;
</a>
@endsection
