<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AAQEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $patient;
    public $srvcReq;
    public $asaq;
    public $payment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($patient, $srvcReq, $asaq, $payment)
    {
        $this->patient = $patient;
        $this->srvcReq = $srvcReq;
        $this->asaq = $asaq;
        $this->payment = $payment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    { 
        return $this->subject("BIRTH - ".$this->srvcReq->service->srvcName." - Service Request Created")->view('mail.user.ask-doctor.confirm');
    }
}
