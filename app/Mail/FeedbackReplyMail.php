<?php

namespace App\Mail;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Feedback $feedback,
        public string $message
    ) {}

    public function build()
    {
        return $this
            ->subject('Re: ' . $this->feedback->subjectLabel() . ' — ВИЗИТ ДОНЕЦК')
            ->replyTo(config('services.mail.admin'), 'ВИЗИТ ДОНЕЦК')
            ->view('emails.feedback-reply');
    }
}
