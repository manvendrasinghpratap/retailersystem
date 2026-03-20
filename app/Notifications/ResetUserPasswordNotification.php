<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetUserPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $resetUrl; // ✅ define property

    public function __construct($resetUrl)
    {
        $this->resetUrl = $resetUrl; // ✅ assign value
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
        // echo $this->resetUrl;
        // echo '<pre>';print_r($notifiable); exit;
        return (new MailMessage)
        ->subject('Reset Your Password')
        ->view('emails.reset-password', [
            'user' => $notifiable,
            'reset_link' => $this->resetUrl,
            'app_name' => config('app.name'),
            'expiry_time' => '60 minutes',
        ]);
        // return (new MailMessage)
        //     ->line('The introduction to the notification.')
        //     ->action('Notification Action', url('/'))
        //     ->line('Thank you for using our application!');
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
