@extends('emails.layouts.default')

@section('title', 'Verify Your May22SMS Account')

@section('preheader', 'Your one-time verification code is ' . $otp . '. Valid for 10 minutes.')

@section('badge')
<span style="display: inline-block; padding: 5px 12px; font-size: 11px; font-weight: 700; color: #0d9488; background-color: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 9999px; letter-spacing: 0.3px; text-transform: uppercase;">
    🔒 Verification
</span>
@endsection

@section('greeting')
Verify Your May22SMS Account
@endsection

@section('content')
<p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 1.7;">
    Hello <strong>{{ $user->name }}</strong>, thank you for joining May22SMS!
</p>
<p style="margin: 0 0 24px 0; font-size: 15px; color: #475569; line-height: 1.7;">
    Please use the one-time security code (OTP) below to verify your email address and activate your account.
</p>

<!-- Sleek Modern OTP Display Card -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0; background: linear-gradient(135deg, #f0fdfa 0%, #f8fafc 100%); border: 2px dashed #0d9488; border-radius: 16px; text-align: center;">
    <tr>
        <td style="padding: 28px 20px;">
            <div style="font-size: 11px; font-weight: 800; color: #0d9488; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px;">
                One-Time Verification Code
            </div>
            
            <div style="font-family: 'SF Mono', 'Courier New', Courier, monospace; font-size: 42px; font-weight: 900; letter-spacing: 10px; color: #0f172a; text-indent: 10px; margin: 8px 0 14px 0; line-height: 1.2;" class="otp-code-text">
                {{ $otp }}
            </div>

            <!-- Expiration & Security Tag -->
            <table border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto;">
                <tr>
                    <td style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 4px 14px; font-size: 12px; font-weight: 600; color: #64748b;">
                        ⏱️ Valid for 10 minutes &bull; Single use only
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Security Alert Callout -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 10px; margin: 24px 0;">
    <tr>
        <td style="padding: 16px 18px;">
            <div style="font-size: 13px; font-weight: 700; color: #b45309; margin-bottom: 4px;">
                Security Reminder
            </div>
            <div style="font-size: 13px; color: #92400e; line-height: 1.5;">
                Never share this verification code with anyone. May22SMS staff and support will <strong>never</strong> ask for your OTP. If you did not request this registration, you can safely ignore this email.
            </div>
        </td>
    </tr>
</table>
@endsection

@section('button')
<!-- Modern Bulletproof CTA Button -->
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/two-factor' }}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="20%" strokecolor="#0d9488" fillcolor="#0d9488">
<w:anchorlock/>
<center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">Complete Verification &rarr;</center>
</v:roundrect>
<![endif]-->
<a href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/two-factor' }}" target="_blank" class="cta-button" style="mso-hide:all; display: inline-block; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35); letter-spacing: 0.2px;">
    Complete Verification &rarr;
</a>
@endsection