<?php

namespace App\Infrastructure\Mail;

use App\Domain\Entities\Ingredient;
use App\Interfaces\StockNotifierInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class StockAlert extends Mailable implements StockNotifierInterface
{
    use Queueable;
    use SerializesModels;

    private $ingredient;

    public function __construct(Ingredient $ingredient)
    {
        $this->ingredient = $ingredient;
    }

    public function notify(Ingredient $ingredient): void
    {
        $this->ingredient = $ingredient;
        Mail::to('merchant@example.com')->queue($this);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Low Stock Alert: {$this->ingredient->getName()}",
            from: 'inventory@foodics.com',
            to: 'merchant@example.com',
            replyTo: 'support@foodics.com'
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.stock_alert',
            with: [
                'ingredient' => $this->ingredient,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
