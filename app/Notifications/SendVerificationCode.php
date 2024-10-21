<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendVerificationCode extends Notification
{
    use Queueable;

    protected $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Verification Code')
            ->greeting('Hello!')
            ->line('Your verification code is: ' . $this->code)
            ->line('Please use this code to verify your account.')
            ->line('Thank you for using our application!');
    }
}
