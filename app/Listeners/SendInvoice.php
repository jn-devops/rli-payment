<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\SendInvoiceNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\PaymentRequested;

class SendInvoice
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentRequested $event): void
    {
        app(User::class)->notify(new SendInvoiceNotification($event->responseData));
    }
}
