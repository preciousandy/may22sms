@extends('emails.layouts.default')

@section('title', 'Welcome to May22SMS!')

@section('preheader', 'Welcome to May22SMS, ' . $user->name . '! Your all-in-one automated digital hub is ready.')

@section('badge')
<span style="display: inline-block; padding: 5px 12px; font-size: 11px; font-weight: 700; color: #0d9488; background-color: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 9999px; letter-spacing: 0.3px; text-transform: uppercase;">
    🚀 Welcome Aboard
</span>
@endsection

@section('greeting')
Welcome to May22SMS, {{ $user->name }}! 🎉
@endsection

@section('content')
<p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 1.7;">
    We're excited to have you join our community! May22SMS is built to empower you with automated SMS verification, instant digital credentials, and social media growth tools — all in one unified, lightning-fast dashboard.
</p>

<p style="margin: 0 0 24px 0; font-size: 15px; font-weight: 700; color: #0f172a;">
    Here is what you can do right away:
</p>

<!-- Feature Showcase Grid (Email-Safe Table Cards) -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px;">
    <!-- Row 1: Wallet & SMS -->
    <tr>
        <td class="mobile-stack" width="50%" valign="top" style="padding: 0 6px 12px 0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; height: 100%;">
                <tr>
                    <td style="padding: 16px 18px;">
                        <div style="font-size: 22px; margin-bottom: 6px;">💳</div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Instant Wallet Top-Up</div>
                        <div style="font-size: 12px; color: #64748b; line-height: 1.5;">Fund your balance effortlessly via multiple secure online channels.</div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="mobile-stack" width="50%" valign="top" style="padding: 0 0 12px 6px;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; height: 100%;">
                <tr>
                    <td style="padding: 16px 18px;">
                        <div style="font-size: 22px; margin-bottom: 6px;">📱</div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">SMS Verifications</div>
                        <div style="font-size: 12px; color: #64748b; line-height: 1.5;">Receive instant OTP codes from 50+ countries and hundreds of apps.</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Row 2: Logs & Boost -->
    <tr>
        <td class="mobile-stack" width="50%" valign="top" style="padding: 0 6px 12px 0;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; height: 100%;">
                <tr>
                    <td style="padding: 16px 18px;">
                        <div style="font-size: 22px; margin-bottom: 6px;">📄</div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Premium Logs</div>
                        <div style="font-size: 12px; color: #64748b; line-height: 1.5;">Access quality verified digital credentials delivered straight to your vault.</div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="mobile-stack" width="50%" valign="top" style="padding: 0 0 12px 6px;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; height: 100%;">
                <tr>
                    <td style="padding: 16px 18px;">
                        <div style="font-size: 22px; margin-bottom: 6px;">🚀</div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Social Media Boost</div>
                        <div style="font-size: 12px; color: #64748b; line-height: 1.5;">Supercharge your online presence with high-speed engagement tools.</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Community Callout -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background: linear-gradient(135deg, #f0fdfa 0%, #f8fafc 100%); border: 1px solid #ccfbf1; border-radius: 12px; margin: 12px 0 20px 0;">
    <tr>
        <td style="padding: 16px 20px;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td valign="middle">
                        <div style="font-size: 13px; font-weight: 700; color: #0f766e; margin-bottom: 2px;">
                            💬 Join Our Official Telegram Community
                        </div>
                        <div style="font-size: 12px; color: #115e59;">
                            Get real-time announcements, flash discount promo codes, and 24/7 community support.
                        </div>
                    </td>
                    <td valign="middle" align="right" style="padding-left: 12px;">
                        <a href="https://t.me/may22sms" target="_blank" style="display: inline-block; background-color: #0d9488; color: #ffffff; text-decoration: none; font-size: 12px; font-weight: 700; padding: 8px 16px; border-radius: 8px; white-space: nowrap;">
                            Join Chat
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection

@section('button')
<!-- Modern Bulletproof CTA Button -->
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/dashboard' }}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="20%" strokecolor="#0d9488" fillcolor="#0d9488">
<w:anchorlock/>
<center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">Explore Your Dashboard &rarr;</center>
</v:roundrect>
<![endif]-->
<a href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/dashboard' }}" target="_blank" class="cta-button" style="mso-hide:all; display: inline-block; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35); letter-spacing: 0.2px;">
    Explore Your Dashboard &rarr;
</a>
@endsection
