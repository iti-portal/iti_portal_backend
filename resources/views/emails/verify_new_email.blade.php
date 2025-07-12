@extends('emails.base')

@section('title', 'Confirm Your New Email Address')

@section('header', 'Confirm Your New Email Address')

@section('content')

<p>Hello,{{ $user->getFullNameAttribute() ?? 'there' }}</p>

<p>We received a request to update your email address on ITI Portal. To complete this change, please confirm your new email address by clicking the button below:</p>

<div style="text-align: center;">
    <a href="{!! $url !!}" class="button">Verify Email Address</a>
</div>

<p class="highlight-note">This verification link will expire in 24 hours.</p>

<p>If you did not request this change, please ignore this email or contact our
    <a href="mailto:support@iti.gov.eg" class="support-link">support team</a>.
</p>

@endsection
