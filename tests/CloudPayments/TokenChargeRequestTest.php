<?php

namespace Tests\Subscriptions;

use Illuminate\Support\Arr;
use Nikservik\Subscriptions\CloudPayments\TokenChargeRequest;
use Tests\TestCase;

class TokenChargeRequestTest extends TestCase
{
    public function testCreate()
    {
        $request= new TokenChargeRequest(12.0, 'RUB', 'user token', 1234, 'test@example.com', 'item');

        $this->assertNotNull($request);
    }

    public function testToArray()
    {
        $request= new TokenChargeRequest(12.0, 'RUB', 'user token', 1234, 'test@example.com', 'item');

        $this->assertIsArray($request->toArray());
    }

    public function testArrayItemParameters()
    {
        $request= new TokenChargeRequest(12.0, 'RUB', 'user token', 1234, 'test@example.com', 'item');

        $this->assertEquals(12.0, Arr::get($request->toArray(), 'Amount'));
        $this->assertEquals('RUB', Arr::get($request->toArray(), 'Currency'));
        $this->assertEquals('item', Arr::get($request->toArray(), 'Description'));
        $this->assertEquals(12.0, Arr::get($request->toArray(), 'JsonData.cloudPayments.CustomerReceipt.Amounts.Electronic'));
    }

    public function testArrayUserParameters()
    {
        $request= new TokenChargeRequest(12.0, 'RUB', 'user token', 1234, 'test@example.com', 'item');

        $this->assertEquals(1234, Arr::get($request->toArray(), 'AccountId'));
        $this->assertEquals(1234, Arr::get($request->toArray(), 'JsonData.cloudPayments.AccountId'));
        $this->assertEquals('test@example.com', Arr::get($request->toArray(), 'JsonData.cloudPayments.CustomerReceipt.Email'));
    }

    public function testArrayToken()
    {
        $request= new TokenChargeRequest(12.0, 'RUB', 'user token', 1234, 'test@example.com', 'item');

        $this->assertEquals('user token', Arr::get($request->toArray(), 'Token'));
    }
}