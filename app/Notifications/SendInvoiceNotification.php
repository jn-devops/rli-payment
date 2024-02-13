<?php

namespace App\Notifications;

use NotificationChannels\Webhook\{WebhookChannel, WebhookMessage};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use App\Data\ResponseData;

class SendInvoiceNotification extends Notification
{
    use Queueable;

    protected ResponseData $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(ResponseData $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [ WebhookChannel::class ];
    }

    public function toWebhook($notifiable): WebhookMessage
    {
        $application = config('app.name');

        return WebhookMessage::create()
            ->data($this->data)
            ->userAgent($application)
            ->header('Accept', 'application/json')
            ;
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
