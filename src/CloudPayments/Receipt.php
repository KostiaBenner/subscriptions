<?php

namespace Nikservik\Subscriptions\CloudPayments;

use Illuminate\Support\Str;

class Receipt
{
    protected $data;

    function __construct($accountId, $email, $items)
    {
        $this->data = [
            'Inn' => config('cloudpayments.inn'),
            'Type' => 'Income',
            'AccountId' => $accountId,
            'CustomerReceipt' => [
                'TaxationSystem' => 1,
                'Email' => $email,
                'CalculationPlace' => $this->withoutHttp(config('app.url')),
                'Amounts' => [],
                'Items' => [],
            ],
        ];

        $this->addItems($items);
    }

    public function toArray()
    {
        return $this->data;
    }

    protected function addItems($items)
    {
        $amount = 0;
        foreach ($items as $item) {
            $this->data['CustomerReceipt']['Items'][] = $item->toArray();
            $amount += $item->amount();
        }
        $this->data['CustomerReceipt']['Amounts'] = [
            'Electronic' => $amount,
        ];
    }

    public function withoutHttp($url)
    {
        return Str::after($url, '//');
    }
}