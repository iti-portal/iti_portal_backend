<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class CustomVerifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
        'verification.verify',
        Carbon::now()->addHours(24),
        [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ]
    );

        return (new MailMessage)
         ->from('portal@iti.com', 'ITI Portal')
         ->subject('Verify Your Email Address')
            ->view('emails.verify_email', [
                'url' => $url,
                'user' => $notifiable,
            ]);

    }
    public function delay9

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */




    /**
     * Get the URL for the notification.
     *
     * @return string
     */


}
