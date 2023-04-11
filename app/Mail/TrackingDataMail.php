<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrackingDataMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject, $message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $subject, $message)
    {
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Build the content.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('emails.trackingDataMail')
            ->with([
                'subject' => $this->subject,
                'message' => $this->message
            ]);
    }
}
