@extends('emails.layouts.default')

@section('title', $status === 'suspended' ? 'Your May22SMS Account Has Been Suspended' : 'Your May22SMS Account Has Been Reactivated')

@section('preheader', $status === 'suspended' ? 'Important security notification regarding your May22SMS account suspension.' : 'Great news! Your May22SMS account has been fully restored and reactivated.')

@section('badge')
@if($status === 'suspended')
<span style="display: inline-block; padding: 5px 12px; font-size: 11px; font-weight: 700; color: #dc2626; background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 9999px; letter-spacing: 0.3px; text-transform: uppercase;">
    ⚠️ Account Suspended
</span>
@else
<span style="display: inline-block; padding: 5px 12px; font-size: 11px; font-weight: 700; color: #16a34a; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 9999px; letter-spacing: 0.3px; text-transform: uppercase;">
    ✓ Account Restored
</span>
@endif
@endsection

@section('greeting')
@if($status === 'suspended')
Important Account Status Notice
@else
Welcome Back, {{ $user->name }}! 🎉
@endif
@endsection

@section('content')
@if($status === 'suspended')
<p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 1.7;">
    Hello <strong>{{ $user->name }}</strong>, we are writing to inform you that your May22SMS account has been temporarily suspended following an automated or administrative review.
</p>

@if(!empty($reason))
<!-- Reason Alert Box -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fef2f2; border-left: 4px solid #ef4444; border-radius: 10px; margin: 24px 0;">
    <tr>
        <td style="padding: 18px 20px;">
            <div style="font-size: 11px; font-weight: 800; color: #dc2626; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">
                Reason for Suspension
            </div>
            <div style="font-size: 15px; font-weight: 700; color: #991b1b; line-height: 1.5;">
                {{ $reason }}
            </div>
        </td>
    </tr>
</table>
@endif

<p style="margin: 0 0 16px 0; font-size: 15px; color: #475569; line-height: 1.7;">
    While your account is suspended, new purchases and wallet withdrawals are restricted. If you believe this action was taken in error or if you have resolved the underlying issue, our support team is ready to assist you.
</p>
@else
<p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 1.7;">
    We are happy to share that your May22SMS account has been thoroughly reviewed and <strong>fully reactivated</strong>.
</p>

<!-- Confirmation Callout -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f0fdf4; border-left: 4px solid #16a34a; border-radius: 10px; margin: 24px 0;">
    <tr>
        <td style="padding: 18px 20px;">
            <div style="font-size: 11px; font-weight: 800; color: #15803d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">
                Account Standing: Active
            </div>
            <div style="font-size: 14px; font-weight: 600; color: #166534; line-height: 1.5;">
                All restrictions have been lifted. You have immediate access to your wallet funds, SMS verification purchases, logs, and social boost services.
            </div>
        </td>
    </tr>
</table>

<p style="margin: 0; font-size: 15px; color: #475569; line-height: 1.7;">
    Thank you for your patience and for being a valued member of the May22SMS community.
</p>
@endif
@endsection

@section('button')
@if($status === 'suspended')
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/support' }}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="20%" strokecolor="#dc2626" fillcolor="#dc2626">
<w:anchorlock/>
<center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">Contact Support Desk &rarr;</center>
</v:roundrect>
<![endif]-->
<a href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/support' }}" target="_blank" class="cta-button" style="mso-hide:all; display: inline-block; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35); letter-spacing: 0.2px;">
    Contact Support Desk &rarr;
</a>
@else
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/dashboard' }}" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="20%" strokecolor="#0d9488" fillcolor="#0d9488">
<w:anchorlock/>
<center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">Go to Dashboard &rarr;</center>
</v:roundrect>
<![endif]-->
<a href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/dashboard' }}" target="_blank" class="cta-button" style="mso-hide:all; display: inline-block; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35); letter-spacing: 0.2px;">
    Go to Dashboard &rarr;
</a>
@endif
@endsection
