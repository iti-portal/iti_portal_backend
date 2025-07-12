<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

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

        // Override the default ResetPassword notification
     ResetPassword::toMailUsing(function ($notifiable, $token) {
        return (new CustomResetPassword($token))
            ->toMail($notifiable);
    });


        // Override the default VerifyEmail notification
        VerifyEmail::toMailUsing(function ($notifiable) {
            return (new CustomVerifyEmail())
                ->toMail($notifiable);
        });



    }}
