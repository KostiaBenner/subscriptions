<?php

namespace Tests\Subscriptions;

use Illuminate\Support\Arr;
use Nikservik\Subscriptions\CloudPayments\CardChargeRequest;
use Tests\TestCase;

class CardChargeRequestTest extends TestCase
{
    public function testCreate()
    {
        $request= new CardChargeRequest(12.0, 'RUB', 'Card Holder', 'crypt packet', '127.0.0.1', 1234, 'test@example.com', 'item');

        $this->assertNotNull($request);
    }

    public function testToArray()
    {
        $request= new CardChargeRequest(12.0, 'RUB', 'Card Holder', 'crypt packet', '127.0.0.1', 1234, 'test@example.com', 'item');

        $this->assertIsArray($request->toArray());
    }

    public function testArrayItemParameters()
    {
        $request= new CardChargeRequest(12.0, 'RUB', 'Card Holder', 'crypt packet', '127.0.0.1', 1234, 'test@example.com', 'item');

        $this->assertEquals(12.0, Arr::get($request->toArray(), 'Amount'));
        $this->assertEquals('RUB', Arr::get($request->toArray(), 'Currency'));
        $this->assertEquals('item', Arr::get($request->toArray(), 'Description'));
        $this->assertEquals(12.0, Arr::get($request->toArray(), 'JsonData.cloudPayments.CustomerReceipt.Amounts.Electronic'));
    }

    public function testArrayUserParameters()
    {
        $request= new CardChargeRequest(12.0, 'RUB', 'Card Holder', 'crypt packet', '127.0.0.1', 1234, 'test@example.com', 'item');

        $this->assertEquals('127.0.0.1', Arr::get($request->toArray(), 'IpAddress'));
        $this->assertEquals('Card Holder', Arr::get($request->toArray(), 'Name'));
        $this->assertEquals(1234, Arr::get($request->toArray(), 'AccountId'));
        $this->assertEquals(1234, Arr::get($request->toArray(), 'JsonData.cloudPayments.AccountId'));
        $this->assertEquals('test@example.com', Arr::get($request->toArray(), 'JsonData.cloudPayments.CustomerReceipt.Email'));
    }

    public function testArrayCrypt()
    {
        $request= new CardChargeRequest(12.0, 'RUB', 'Card Holder', 'crypt packet', '127.0.0.1', 1234, 'test@example.com', 'item');

        $this->assertEquals('crypt packet', Arr::get($request->toArray(), 'CardCryptogramPacket'));
    }
}