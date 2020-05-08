<?php

namespace Nikservik\Subscriptions\CloudPayments;

class Post3dsRequest
{
    protected $data;

    function __construct($transactionId, $paRes)
    {
        $this->data = [
            'TransactionId' => $transactionId,
            'PaRes' => $paRes,
        ];
    }

    public function toArray()
    {
        return $this->data;
    }
}