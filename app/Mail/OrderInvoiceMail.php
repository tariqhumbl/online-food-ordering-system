<?php

namespace App\Mail;

use App\Models\FoodOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public FoodOrder $order;

    public function __construct(FoodOrder $order)
    {
        $this->order = $order->load(['restaurant:id,name', 'user:id,name,email', 'items']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your order invoice – ' . $this->order->order_number . ' | ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_invoice',
        );
    }

    public function attachments(): array
    {
        $order = $this->order;
        $filename = 'invoice-' . preg_replace('/[^a-zA-Z0-9\-_]/', '-', $this->order->order_number) . '.pdf';

        return [
            Attachment::fromData(function () use ($order) {
                $pdf = Pdf::loadView('pdf.order-invoice', ['order' => $order])
                    ->setPaper('a4', 'portrait')
                    ->setOption('isRemoteEnabled', true);

                return $pdf->output();
            }, $filename)->withMime('application/pdf'),
        ];
    }
}
