<?php

namespace Nikservik\Subscriptions\CloudPayments;

use Nikservik\Subscriptions\CloudPayments\Receipt;
use Nikservik\Subscriptions\CloudPayments\ReceiptItem;

class CardChargeRequest
{
    protected $data;

    function __construct($price, $currency, $name, $crypt, $ip, $userId, $email, $description)
    {
        $this->data = [
            'Amount' => $price, 
            'Currency' => $currency, 
            'IpAddress' => $ip, 
            'Name' => $name, 
            'CardCryptogramPacket' => $crypt, 
            'Description' => $description,
            'AccountId' => $userId,
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