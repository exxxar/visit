<?php

namespace App\Mail;

use App\Models\Place;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlaceApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Place $place,
        public User $owner
    ) {}

    public function build()
    {
        return $this
            ->subject('🎉 Ваше заведение опубликовано на ВИЗИТ ДОНЕЦК')
            ->view('emails.place-approved');
    }
}
