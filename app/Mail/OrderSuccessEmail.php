<?php

namespace App\Mail;

use App\Enums\EmailTypes;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSuccessEmail extends Mailable
{
    use Queueable, SerializesModels;

    public bool $hasUrl;

    /**
     * The email confirms, as the law asks, that the buyer asked for immediate delivery and gave up the right of
     * withdrawal, and links to the terms the purchase is governed by.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return void
     */
    public function __construct(
        public array $lines,
        public ?string $userName,
        public bool $immediateDelivery = false,
        public ?string $termsUrl = null,
    ) {
        $this->hasUrl = collect($lines)->some(fn (array $line) => ! empty($line['url']));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: config('mail.contact_email'),
            subject: tConstValue(EmailTypes::class, EmailTypes::ORDER_SUCCESS),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order_success',
        );
    }
}
