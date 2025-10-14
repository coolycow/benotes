<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public string $code)
    {
        $this->queue = 'mail';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): CodeMail
    {
        return $this->markdown('mail.code')->subject('Confirmation code');
    }
}
