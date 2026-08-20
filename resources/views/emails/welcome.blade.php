@extends('emails.layouts.default')

@section('title', 'Welcome to may22sms!')

@section('greeting')
Welcome to may22sms, {{ $user->name }}! 🎉
@endsection

@section('content')
<p>We're thrilled to have you join our community of smart users! may22sms makes it easy to buy SMS numbers, logs, and boosting services without any stress.</p>

<p style="margin-top: 16px;">Here's what you can do right away:</p>
<ul style="margin: 16px 0; padding-left: 20px; color: #4b5563;">
    <li style="margin-bottom: 8px;">Top up your wallet using any of our payment methods</li>
    <li style="margin-bottom: 8px;">Buy SMS numbers for verification purposes</li>
    <li style="margin-bottom: 8px;">Purchase log credentials</li>
    <li>Boost your social media presence</li>
</ul>
@endsection

@section('button')
<a href="{{ url('/dashboard') }}" class="cta-button">Go to Dashboard</a>
@endsection
