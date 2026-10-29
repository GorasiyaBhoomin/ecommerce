<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class orderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Details #'.$this->order->id
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-mail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
