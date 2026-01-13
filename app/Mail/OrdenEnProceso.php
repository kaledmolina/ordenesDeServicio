<?php

namespace App\Mail;

use App\Models\Orden;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrdenEnProceso extends Mailable
{
    use Queueable, SerializesModels;

    public $orden;

    public function __construct(Orden $orden)
    {
        $this->orden = $orden;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Su Técnico está en Camino - Orden #' . $this->orden->numero_orden,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ordenes.en_proceso',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
