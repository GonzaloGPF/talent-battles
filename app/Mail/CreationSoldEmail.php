<?php

namespace App\Mail;

use App\Enums\EmailTypes;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreationSoldEmail extends Mailable
{
    use Queueable, SerializesModels;

    public float $total;

    public float $commission;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public string $userName, public array $lines)
    {
        $this->total = collect($lines)->sum('total');
        $this->commission = config('company.fees.creation');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: config('mail.contact_email'),
            subject: tConstValue(EmailTypes::class, EmailTypes::CREATION_SOLD),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.creation_sold',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
