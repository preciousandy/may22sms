@extends('emails.layouts.default')

@section('title', 'Your Wallet Has Been Funded Successfully!')

@section('greeting')
Hi {{ $user->name }}, great news! 💰
@endsection

@section('content')
<p>Your wallet has been successfully funded! The funds are now available in your account and ready to use.</p>

<div class="divider"></div>

<div style="background-color: #f9fafb; padding: 24px; border-radius: 12px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
            <div style="color: #6b7280; font-size: 14px;">Amount Added</div>
            <div style="color: #0d9488; font-size: 24px; font-weight: 800;">${{ number_format($amount, 2) }}</div>
        </div>
        <div>
            <div style="color: #6b7280; font-size: 14px;">Payment Method</div>
            <div style="color: #111827; font-size: 18px; font-weight: 700;">{{ ucfirst($funding->payment_method) }}</div>
        </div>
        <div>
            <div style="color: #6b7280; font-size: 14px;">Transaction ID</div>
            <div style="color: #111827; font-size: 14px; font-weight: 600; font-family: monospace;">{{ $funding->id }}</div>
        </div>
        <div>
            <div style="color: #6b7280; font-size: 14px;">Date</div>
            <div style="color: #111827; font-size: 14px; font-weight: 600;">{{ $funding->created_at->format('M d, Y h:i A') }}</div>
        </div>
    </div>
</div>
@endsection

@section('button')
<a href="{{ url('/wallet') }}" class="cta-button">View Wallet Balance</a>
@endsection
