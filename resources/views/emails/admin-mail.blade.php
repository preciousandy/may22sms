@extends('emails.layouts.default')

@section('title', $emailSubject)

@section('greeting')
@endsection

@section('content')
<div style="padding: 40px 30px; background: #ffffff;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 24px; line-height: 1.3;">
            {{ $emailSubject }}
        </div>
        <div style="font-size: 16px; color: #4b5563; line-height: 1.8; margin-bottom: 28px;">
            {!! nl2br(e($emailBody)) !!}
        </div>
        <div style="padding: 24px; background: #f3f4f6; border-radius: 12px; text-align: center; margin-bottom: 28px;">
            <div style="font-size: 14px; color: #6b7280; font-weight: 500;">
                If you didn't expect this email, please contact support.
            </div>
        </div>
    </div>
</div>
@endsection
