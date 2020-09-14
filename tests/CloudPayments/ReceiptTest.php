<?php

namespace Tests\Subscriptions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Nikservik\Subscriptions\CloudPayments\Receipt;
use Nikservik\Subscriptions\CloudPayments\ReceiptItem;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    public function testCreate()
    {
        $receipt = new Receipt(1234, 'test@example.com', [new ReceiptItem('item', 12.0)]);

        $this->assertNotNull($receipt);
    }

    public function testToArray()
    {
        $receipt = new Receipt(1234, 'test@example.com', [new ReceiptItem('item', 12.0)]);

        $this->assertIsArray($receipt->toArray());
    }

    public function testArrayAccountingParameters()
    {
        Config::set('cloudpayments.inn', 123456789);

        $receipt = new Receipt(1234, 'test@example.com', [new ReceiptItem('item', 12.0)]);

        $this->assertEquals(123456789, Arr::get($receipt->toArray(), 'Inn'));
        $this->assertEquals('Income', Arr::get($receipt->toArray(), 'Type'));
        $this->assertEquals(1, Arr::get($receipt->toArray(), 'CustomerReceipt.TaxationSystem'));
    }

    public function testArrayEmail()
    {
        $receipt = new Receipt(1234, 'test@example.com', [new ReceiptItem('item', 12.0)]);

        $this->assertEquals('test@example.com', Arr::get($receipt->toArray(), 'CustomerReceipt.Email'));
    }

    public function testArrayAccountId()
    {
        $receipt = new Receipt(1234, 'test@example.com', [new ReceiptItem('item', 12.0)]);

        $this->assertEquals(1234, Arr::get($receipt->toArray(), 'AccountId'));
    }

    public function testArrayCalculationPlace()
    {
        Config::set('app.url', 'https://example.com');

        $receipt = new Receipt(1234, 'test@example.com', [new ReceiptItem('item', 12.0)]);

        $this->assertEquals('example.com', Arr::get($receipt->toArray(), 'CustomerReceipt.CalculationPlace'));
    }

    public function testArrayAmounts()
    {
        $receipt = new Receipt(1234, 'test@example.com', [new ReceiptItem('item1', 12.0), new ReceiptItem('item2', 24.50)]);

        $this->assertEquals(36.50, Arr::get($receipt->toArray(), 'CustomerReceipt.Amounts.Electronic'));
    }

    public function testArrayItems()
    {
        $receipt = new Receipt(1234, 'test@example.com', [new ReceiptItem('item1', 12.0), new ReceiptItem('item2', 24.50)]);

        $this->assertEqualsCanonicalizing(
            ['item1', 'item2'], 
            Arr::pluck(Arr::get($receipt->toArray(), 'CustomerReceipt.Items'), 'Label')
        );
    }
}