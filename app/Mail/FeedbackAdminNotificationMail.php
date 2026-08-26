<?php

namespace App\Mail;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackAdminNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Feedback $feedback) {}

    public function build()
    {
        return $this
            ->subject('🔔 Новое обращение с сайта: ' . $this->feedback->subjectLabel())
            ->view('emails.feedback-admin-notification');
    }
}
