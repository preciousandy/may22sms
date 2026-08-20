@extends('emails.layouts.default')

@section('title', 'Your SMS Number Purchase is Complete!')

@section('greeting')
Hi {{ $user->name }}, your SMS number is ready! 📱
@endsection

@section('content')
<p>Your SMS number purchase has been processed successfully! You can now use your number for verification purposes.</p>

<div class="divider"></div>

<div style="background-color: #f9fafb; padding: 24px; border-radius: 12px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
            <div style="color: #6b7280; font-size: 14px;">Order ID</div>
            <div style="color: #111827; font-size: 16px; font-weight: 700;">#{{ $order->id }}</div>
        </div>
        <div>
            <div style="color: #6b7280; font-size: 14px;">Total Amount</div>
            <div style="color: #0d9488; font-size: 20px; font-weight: 800;">${{ number_format($order->total_amount, 2) }}</div>
        </div>
        <div>
            <div style="color: #6b7280; font-size: 14px;">Country</div>
            <div style="color: #111827; font-size: 16px; font-weight: 700;">{{ $order->country_name }}</div>
        </div>
        <div>
            <div style="color: #6b7280; font-size: 14px;">Date</div>
            <div style="color: #111827; font-size: 14px; font-weight: 600;">{{ $order->created_at->format('M d, Y h:i A') }}</div>
        </div>
    </div>
</div>
@endsection

@section('button')
<a href="{{ url('/orders') }}" class="cta-button">View My Orders</a>
@endsection
