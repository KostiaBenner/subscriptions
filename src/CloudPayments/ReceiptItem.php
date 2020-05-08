<?php

namespace Nikservik\Subscriptions\CloudPayments;

class ReceiptItem
{
    protected $data;

    function __construct($label, $price)
    {
        $this->data = [
            'Label' => $label,
            'Price' => $price,
            'Quantity' => 1,
            'Vat' => null,
            'Object' => 4, // услуга
            'Method' => 4, // полная оплата
            'Amount' => $price,
        ];
    }

    public function amount()
    {
        return $this->data['Amount'];
    }

    public function toArray()
    {
        return $this->data;
    }
}
