<?php
namespace App\Mail;

use App\Models\Clearance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentClearanceApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Clearance $clearance;

    /**
     * Create a new message instance.
     */
    public function __construct(Clearance $clearance)
    {
        $this->clearance = $clearance;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $studentName = optional($this->clearance->student)->name ?? 'Student';

        return $this->subject("Your clearance has been approved")
            ->view('emails.clearance-approved')
            ->with([
                'clearance' => $this->clearance,
                'studentName' => $studentName,
            ]);
    }
}
