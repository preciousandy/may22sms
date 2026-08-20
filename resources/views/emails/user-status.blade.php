@extends('emails.layouts.default')

@section('title', $status === 'suspended' ? 'Your Account Has Been Suspended' : 'Your Account Has Been Reactivated')

@section('greeting')
@if($status === 'suspended')
Important Update About Your Account
@else
Great News, {{ $user->name }}! 🎉
@endif
@endsection

@section('content')
@if($status === 'suspended')
<p>We regret to inform you that your may22sms account has been temporarily suspended.</p>

@if($reason)
<div class="divider"></div>
<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; border-radius: 8px; margin: 24px 0;">
    <div style="color: #dc2626; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Reason for Suspension</div>
    <div style="color: #991b1b; font-size: 16px; font-weight: 600;">{{ $reason }}</div>
</div>
@endif

<p style="margin-top: 16px;">If you believe this is a mistake or have any questions, please contact our support team immediately.</p>
@else
<p>Great news! Your may22sms account has been reactivated, and you can now use all our services again.</p>

<p style="margin-top: 16px;">We're happy to have you back! If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
@endif
@endsection

@section('button')
@if($status === 'suspended')
<a href="{{ url('/support') }}" class="cta-button" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">Contact Support</a>
@else
<a href="{{ url('/dashboard') }}" class="cta-button">Go to Dashboard</a>
@endif
@endsection
