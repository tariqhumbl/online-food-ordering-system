<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManagerCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $password;
    public string $restaurantName;
    public string $loginUrl;

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->restaurantName = $data['restaurant_name'] ?? '';
        $this->loginUrl = $data['login_url'] ?? '';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your restaurant manager account – ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.manager_credentials',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
