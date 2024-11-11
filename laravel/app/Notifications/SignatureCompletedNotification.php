<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SignatureCompletedNotification extends Notification
{
    use Queueable;

    private $signatureRequest;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\SignatureRequest  $signatureRequest
     * @return void
     */
    public function __construct($signatureRequest)
    {
        $this->signatureRequest = $signatureRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Layanan Prodi Fakultas Sains dan Teknologi: Dokumen Anda Telah Ditandatangani')
            ->markdown('emails.completed-signature', [
                'signatureRequest' => $this->signatureRequest,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
