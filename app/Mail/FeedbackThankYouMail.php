<?php

namespace App\Mail;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackThankYouMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Feedback $feedback) {}

    public function build()
    {
        return $this
            ->subject('✉ Спасибо! Ваше сообщение доставлено — ВИЗИТ ДОНЕЦК')
            ->view('emails.feedback-thank-you');
    }
}
