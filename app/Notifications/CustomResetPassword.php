<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPassword extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $token;
    public function __construct($token)
    {
        $this->token = $token;
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
       $frontendBaseUrl = config('app.frontend_url') . '/reset-password';
        $token = $this->token;
        $email = urlencode($notifiable->email);

        $url = "{$frontendBaseUrl}?token={$token}&email={$email}";

        return (new MailMessage)
            ->from('portal@iti.com', 'ITI Portal')
            ->subject('Reset Password Notification')
            ->view('emails.reset_password', [
                'url'   => $url,
                'token' => $token,
                'email' => $notifiable->email,
            ]);
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
