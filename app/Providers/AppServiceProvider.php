<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
      VerifyEmail::toMailUsing(function ($notifiable, $url) {
        return (new MailMessage)
            ->from('portal@iti.com', 'ITI Portal')
            ->subject('Verify Email Address for ITI Portal')
            ->view('emails.verify-email', ['url' => $url]);
    });
    }
}
