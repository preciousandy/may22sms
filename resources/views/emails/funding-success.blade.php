@extends('emails.layouts.default')

@section('title', 'Wallet Credited Successfully!')

@section('preheader', 'Your wallet has been credited with $' . number_format($amount, 2) . ' USD. Ready to use immediately.')

@section('badge')
<span style="display: inline-block; padding: 5px 12px; font-size: 11px; font-weight: 700; color: #059669; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 9999px; letter-spacing: 0.3px; text-transform: uppercase;">
    ✓ Payment Success
</span>
@endsection

@section('greeting')
Your Wallet Has Been Funded! 💳
@endsection

@section('content')
<p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 1.7;">
    Hi <strong>{{ $user->name }}</strong>, great news! We have confirmed your payment and credited your wallet balance.
</p>

<!-- Hero Amount Receipt Card -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 24px 0; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border: 1px solid #bbf7d0; border-radius: 16px; overflow: hidden; text-align: center;">
    <tr>
        <td style="padding: 28px 20px;">
            <div style="font-size: 12px; font-weight: 800; color: #15803d; text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 6px;">
                Amount Credited
            </div>
            <div style="font-size: 38px; font-weight: 900; color: #0f172a; letter-spacing: -1px; margin: 0 0 8px 0; line-height: 1.2;">
                +${{ number_format($amount, 2) }} <span style="font-size: 20px; font-weight: 700; color: #16a34a;">USD</span>
            </div>
            <div style="display: inline-block; background-color: #dcfce7; border: 1px solid #86efac; border-radius: 9999px; padding: 3px 12px; font-size: 11px; font-weight: 700; color: #166534;">
                Instant Automated Balance Update
            </div>
        </td>
    </tr>
</table>

<!-- Clean Modern Transaction Receipt Table -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin: 24px 0;" class="email-data-box">
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Transaction ID</td>
                    <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 700; font-family: 'SF Mono', monospace;">#{{ $funding->reference ?? $funding->id }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Payment Method</td>
                    <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 700;">{{ ucfirst(str_replace('_', ' ', $funding->payment_method ?? 'Online Payment')) }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Status</td>
                    <td align="right">
                        <span style="display: inline-block; background-color: #dcfce7; color: #166534; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                            Completed
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
                    <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 600;">{{ $funding->created_at ? $funding->created_at->format('M d, Y • h:i A') : now()->format('M d, Y • h:i A') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0; font-size: 14px; color: #64748b; line-height: 1.6;">
    Your wallet balance is active and ready to use for ordering instant SMS verifications, downloading log credentials, or placing social media boost orders.
</p>
@endsection

@section('button')
<!-- Modern Bulletproof CTA Button -->
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/wallet' }}" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="20%" strokecolor="#0d9488" fillcolor="#0d9488">
<w:anchorlock/>
<center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">View Wallet Balance &rarr;</center>
</v:roundrect>
<![endif]-->
<a href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/wallet' }}" target="_blank" class="cta-button" style="mso-hide:all; display: inline-block; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35); letter-spacing: 0.2px;">
    View Wallet Balance &rarr;
</a>
@endsection
