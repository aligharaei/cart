<?php

namespace App\Listeners;

use App\Events\ProductCreatedEvent;
use App\Mail\ProductCreated;
use Illuminate\Support\Facades\Mail;

class SendProductCreatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(ProductCreatedEvent $event): void
    {
        Mail::to('ali.gharaei360@gmail.com')->send(new ProductCreated($event->product));
    }
}
