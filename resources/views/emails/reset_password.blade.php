@extends('emails.base')

@section('title', 'Password Reset Request')

@section('header', 'ITI Portal Password Reset')

@section('content')
    <p>Hello {{ $user->getFullNameAttribute() ?? 'there' }},</p>
    <p>We received a request to reset the password for your ITI Portal account.</p>
    <p>Click the button below to reset your password:</p>

    <div>
        <a href="{{ $url }}" class="button-primary">Reset Password</a>
    </div>

    <p class="highlight-note">This password reset link will expire in 60 minutes.</p>

    <p>If you didn't request this password reset, please ignore this email or contact our <a href="mailto:support@iti.gov.eg"
            class="support-link">support team</a> if you have any concerns.</p>
@endsection
