<?php

namespace Modules\HRMS\App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class SalarySlipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee, $total, $payrolls;

    public function __construct($employee, $total, $payrolls)
    {
        $this->employee = $employee;
        $this->total = $total;
        $this->payrolls = $payrolls;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Salary Slip Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'hrms::payroll.simple_mail',
            with: [
                'employee' => $this->employee,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('hrms::payroll.bulk_payslip_pdf', [
            'payrolls' => $this->payrolls
        ]);

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                'salary_slip.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
