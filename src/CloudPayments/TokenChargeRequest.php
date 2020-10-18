<?php

namespace Nikservik\Subscriptions\CloudPayments;

class TokenChargeRequest
{
    protected $data;

    function __construct($price, $currency, $token, $userId, $email, $subscriptionId, $description)
    {
        $this->data = [
            'Amount' => $price, 
            'Currency' => $currency, 
            'AccountId' => $userId,
            'InvoiceId' => $subscriptionId,
            'Token' => $token, 
            'Description' => $description,
            'JsonData' => [
                'cloudPayments' => (new Receipt($userId, $email, 
                    [new ReceiptItem($description, $price)]))->toArray()
            ],
        ];
    }

    public function toArray()
    {
        return $this->data;
    }
}