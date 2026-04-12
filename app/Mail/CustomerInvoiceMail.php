<?php

namespace App\Mail;

use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;
    public $customer;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale, Customer $customer)
    {
        $this->sale = $sale;
        $this->customer = $customer;
    }

    /**
     * Build the message
     */
    public function build()
    {
        $subject = \Config::get('constants.shop_name') . ' - ' . $this->sale->invoice_type . ' #' . $this->sale->invoice_no;
        return $this->subject($subject)
            ->view('emails.invoice') // ✅ correct view
            ->with([
                'invoice_no' => $this->sale->invoice_no,
                'sale' => $this->sale,
                'customer' => $this->customer,
                'items' => $this->sale->items,
                'date' => $this->sale->created_at,
                'time' => $this->sale->created_at,
            ]);
    }
}