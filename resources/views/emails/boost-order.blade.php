@extends('emails.layouts.default')

@section('title', 'Your Boost Order Has Been Placed!')

@section('preheader', 'Your social media boost order #' . $order->id . ' has been initiated and queued for delivery.')

@section('badge')
<span style="display: inline-block; padding: 5px 12px; font-size: 11px; font-weight: 700; color: #ea580c; background-color: #fff7ed; border: 1px solid #ffedd5; border-radius: 9999px; letter-spacing: 0.3px; text-transform: uppercase;">
    🚀 Boost Queued
</span>
@endsection

@section('greeting')
Your Boost Order is On The Way! 🚀
@endsection

@section('content')
<p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 1.7;">
    Hi <strong>{{ $user->name }}</strong>, your social media boost order has been placed successfully! Our high-speed delivery servers have initiated the campaign.
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
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Service</td>
                    <td align="right" style="font-size: 13px; color: #0f172a; font-weight: 700;">
                        {{ optional($order->service)->name ?? optional($order->boostService)->name ?? 'Social Boost Service' }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    @if(!empty($order->quantity))
    <tr>
        <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13px; color: #64748b; font-weight: 600;">Quantity</td>
                    <td align="right" style="font-size: 13px; color: #0d9488; font-weight: 700;">{{ number_format($order->quantity) }}</td>
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
                    <td align="right" style="font-size: 14px; color: #0f172a; font-weight: 800;">${{ number_format($order->charge ?? $order->total_amount ?? 0, 2) }} USD</td>
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

<!-- Delivery Guidance Callout -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fff7ed; border-left: 4px solid #ea580c; border-radius: 10px; margin: 24px 0;">
    <tr>
        <td style="padding: 16px 18px;">
            <div style="font-size: 13px; font-weight: 700; color: #c2410c; margin-bottom: 4px;">
                ⚡ Live Campaign Progress
            </div>
            <div style="font-size: 13px; color: #9a3412; line-height: 1.5;">
                Most boost orders begin processing within minutes. You can check start count, delivery progress, and completion status directly in your dashboard orders panel.
            </div>
        </td>
    </tr>
</table>
@endsection

@section('button')
<!-- Modern Bulletproof CTA Button -->
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/orders?type=boost' }}" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="20%" strokecolor="#0d9488" fillcolor="#0d9488">
<w:anchorlock/>
<center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">Track Boost Order &rarr;</center>
</v:roundrect>
<![endif]-->
<a href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/orders?type=boost' }}" target="_blank" class="cta-button" style="mso-hide:all; display: inline-block; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35); letter-spacing: 0.2px;">
    Track Boost Order &rarr;
</a>
@endsection
