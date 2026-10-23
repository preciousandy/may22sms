@extends('emails.layouts.default')

@section('title', $emailSubject)

@section('preheader', \Illuminate\Support\Str::limit(strip_tags($emailBody), 120))

@section('badge')
<span style="display: inline-block; padding: 5px 12px; font-size: 11px; font-weight: 700; color: #0d9488; background-color: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 9999px; letter-spacing: 0.3px; text-transform: uppercase;">
    📢 Announcement
</span>
@endsection

@section('greeting')
{{ $emailSubject }}
@endsection

@section('content')
<!-- Formatted Broadcast Body -->
<div style="font-size: 15px; color: #334155; line-height: 1.8; margin-bottom: 28px;">
    {!! nl2br(e($emailBody)) !!}
</div>

<!-- Support Reassurance Callout -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin: 24px 0;" class="email-data-box">
    <tr>
        <td style="padding: 16px 20px; text-align: center;">
            <div style="font-size: 13px; color: #64748b; font-weight: 500;">
                If you have questions regarding this announcement or did not expect this message, feel free to contact our <a href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/support' }}" target="_blank" style="color: #0d9488; font-weight: 600; text-decoration: none;">Support Desk</a>.
            </div>
        </td>
    </tr>
</table>
@endsection

@section('button')
<!-- Modern Bulletproof CTA Button -->
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/dashboard' }}" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="20%" strokecolor="#0d9488" fillcolor="#0d9488">
<w:anchorlock/>
<center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">Open Dashboard &rarr;</center>
</v:roundrect>
<![endif]-->
<a href="{{ ($emailPublicUrl ?? 'https://may22sim.com') . '/dashboard' }}" target="_blank" class="cta-button" style="mso-hide:all; display: inline-block; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35); letter-spacing: 0.2px;">
    Open Dashboard &rarr;
</a>
@endsection
