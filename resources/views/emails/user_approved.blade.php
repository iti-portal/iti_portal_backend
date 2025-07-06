@extends('emails.base')

@section('title', 'Account Approved')
@section('header', 'Your Account Has Been Approved')

@section('content')
    <p>Dear {{ $user->getFullNameAttribute() }},</p>

    <p>We are pleased to inform you that your account has been <strong>approved</strong> by the ITI administration. </p>

    <p>Thank you for your patience during the approval process. We are excited to have you as part of our community.</p>

    <p style="margin-bottom: 25px;">You can now access:</p>

    <div style="margin-bottom: 30px;">
        <a href="{{ config('app.frontend_url') }}/login"
           style="background-color: #901b20; color: white; padding: 14px 30px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block; margin-right: 15px; text-align: center; min-width: 140px;">
            ITI Portal
        </a>

        <a href="{{ config('app.events_url') }}/login"
           style="background-color: #901b20; color: white; padding: 14px 30px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block; text-align: center; min-width: 140px;">
            ITI Events
        </a>
    </div>

    <p>If you have any questions, feel free to <a href="mailto:support@iti.gov.eg" class="support-link">contact our support team</a>.</p>


@endsection
