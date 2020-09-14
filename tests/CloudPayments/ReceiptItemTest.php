<?php

namespace Tests\Subscriptions;

use Illuminate\Support\Arr;
use Nikservik\Subscriptions\CloudPayments\ApiResponseFactory;
use Nikservik\Subscriptions\CloudPayments\ReceiptItem;
use Tests\TestCase;

class ReceiptItemTest extends TestCase
{
    public function testCreate()
    {
        $item = new ReceiptItem('label', 12.0);

        $this->assertNotNull($item);
    }

    public function testGetAmount()
    {
        $item = new ReceiptItem('label', 12.0);

        $this->assertEquals(12.0, $item->amount());
    }

    public function testToArray()
    {
        $item = new ReceiptItem('label', 12.0);

        $this->assertIsArray($item->toArray());
    }

    public function testArrayItemParameters()
    {
        $price = 12.0;
        
        $item = new ReceiptItem('item', $price);

        $this->assertEquals('item', Arr::get($item->toArray(), 'Label'));
        $this->assertEquals($price, Arr::get($item->toArray(), 'Price'));
        $this->assertEquals($price, Arr::get($item->toArray(), 'Amount'));
        $this->assertEquals(1, Arr::get($item->toArray(), 'Quantity'));
    }

    public function testArrayAccountingParameters()
    {
        $item = new ReceiptItem('label', 12.0);

        $this->assertNull(Arr::get($item->toArray(), 'Vat'));
        $this->assertEquals(4, Arr::get($item->toArray(), 'Object'));
        $this->assertEquals(4, Arr::get($item->toArray(), 'Method'));
    }
}