<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DemoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    /**
     * Create a new message instance.
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
        Log::info('DemoMail constructed with data: ', $mailData);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailData['subject'] ?? 'Password Reset - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Check if view exists, fallback to text if needed
        if (view()->exists('emails.demo')) {
            return new Content(
                html: 'emails.demo',
            );
        } else {
            Log::warning('Email template emails.demo not found, using text format');
            return new Content(
                text: 'emails.demo-text',
            );
        }
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

    // Fallback build method for older Laravel versions
    public function build()
    {
        Log::info('Building email with subject: ' . ($this->mailData['subject'] ?? 'Default Subject'));
        
        return $this->subject($this->mailData['subject'] ?? 'Password Reset - ' . config('app.name'))
                    ->view('emails.demo')
                    ->with('mailData', $this->mailData);
    }
}
