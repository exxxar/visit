<?php

namespace App\Mail;

use App\Models\Place;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlaceApprovedWithCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Place $place,
        public User $owner,
        public string $password
    ) {}

    public function build()
    {
        return $this
            ->subject('🎉 Заведение опубликовано + доступ к личному кабинету')
            ->view('emails.place-approved-with-credentials');
    }
}
