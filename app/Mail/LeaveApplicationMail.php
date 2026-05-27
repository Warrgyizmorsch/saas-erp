<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leave;
    public $employee;

    public function __construct($leave, $employee)
    {
        $this->leave = $leave;
        $this->employee = $employee;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Leave Application: ' . $this->employee->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'hrms::leave.leave_mail',
        );
    }
}
