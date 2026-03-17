<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailResetPasswordRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $heading;
    public $name;
    public $code;

    /**
     * Create a new message instance.
     *
     * @param array $codeData
     */
    public function __construct($codeData)
    {
        // Assign the incoming data to class properties
        $this->heading = $codeData['heading'];
        $this->name = $codeData['name'];
        $this->code = $codeData['code'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mail Reset Password Request',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset_password_request',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
