<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FundingSuccessEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $funding;
    public $amount;

    public function __construct($user, $funding, $amount)
    {
        $this->user = $user;
        $this->funding = $funding;
        $this->amount = $amount;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Wallet Has Been Funded Successfully! 💳',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.funding-success',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
