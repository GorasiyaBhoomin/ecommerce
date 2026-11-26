<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alerts;

    public function __construct(Collection $alerts)
    {
        $this->alerts = $alerts;
    }

    public function build()
    {
        return $this->subject('Shopkart - Low Stock Alert')
            ->view('emails.low-stock-alert');
    }
}
