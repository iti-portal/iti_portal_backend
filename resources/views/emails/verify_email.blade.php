@extends('emails.base')

@section('title', 'Verify Your Email Address')

@section('header', 'Verify Your Email Address')

@section('content')
    <p>Hello,{{ $user->getFullNameAttribute() ?? 'there' }}</p>
    <p>Thank you for registering with ITI Portal. To complete your registration, please verify your email address by
        clicking the button below:</p>

    <div style="text-align: center;">
        <a href="{{ $url }}" class="button">Verify Email Address</a>
    </div>

    <p class="highlight-note">This verification link will expire in 24 hours</p>

    <p>If you didn't request this, please ignore this email or contact our
        <a href="mailto:support@iti.gov.eg" class="support-link">support team</a> if you have any concerns.
    </p>

@endsection
