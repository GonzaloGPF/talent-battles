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
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public array $lines, public ?string $userName)
    {
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
