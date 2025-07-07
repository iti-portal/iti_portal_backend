@extends('emails.base')

@section('title', 'Account Approved')
@section('header', 'Your Account Has Been Approved')

@section('content')
    <p>Dear {{ $user->getFullNameAttribute() ?? 'there' }},</p>

    <p>We are pleased to inform you that your account has been <strong>approved</strong> by the ITI administration.</p>

    <p>Thank you for your patience during the approval process. We are excited to have you as part of our community.</p>

    <p >You can now access:</p>

    <div >
        <a href="{{ config('app.frontend_url') }}/login" class="button-primary">
            ITI Portal
        </a>

        <a href="{{ config('app.events_url') }}/login" class="button-secondary">
            ITI Events
        </a>
    </div>

    <p style="text-align: center;">
        If you have any questions, feel free to
        <a href="mailto:support@iti.gov.eg" class="support-link">contact our support team</a>.
    </p>
@endsection
