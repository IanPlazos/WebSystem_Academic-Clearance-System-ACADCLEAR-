<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UniversityCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $adminEmail,
        public string $adminPassword,
        public string $planName,
        public float $amountPaid,
        public CarbonInterface $startsAt,
        public CarbonInterface $endsAt,
        public string $paymentMethod,
        public string $domain,
        public string $loginUrl
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your AcadClear University Account Is Ready'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.university-credentials'
        );
    }
}
