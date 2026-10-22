<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PromotionRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $contactName,
        public string $placeName,
        public array $requests,
        public float $totalAmount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваша заявка на продвижение принята — КОД НОВОРОССИИ',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.promotion.created',
        );
    }
}
