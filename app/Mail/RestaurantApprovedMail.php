<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RestaurantApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $restaurantName;
    public string $loginUrl;

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->restaurantName = $data['restaurant_name'] ?? '';
        $this->loginUrl = $data['login_url'] ?? '';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your restaurant has been approved – ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.restaurant_approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
