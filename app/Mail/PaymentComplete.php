<?php

namespace App\Mail;

use App\Model\Order;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentComplete extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order, $receipt_data, $order_items, $subject)
    {
        $this->order = $order;
        $this->receipt_data = $receipt_data;
        $this->order_items = $order_items;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order = $this->order;
        $receipt_data = $this->receipt_data;
        $order_items = $this->order_items;
        $subject = $this->subject;

        return $this->subject($subject)->view('frontend.mail.paymentComplete', compact('order', 'receipt_data', 'order_items'));
    }
}
