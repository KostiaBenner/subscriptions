<?php

namespace Tests\Subscriptions;

use Illuminate\Support\Arr;
use Nikservik\Subscriptions\CloudPayments\Post3dsRequest;
use Tests\TestCase;

class Post3dsRequestTest extends TestCase
{
    public function testCreate()
    {
        $request= new Post3dsRequest(123456789, 'https://www.example.com/3ds/check');

        $this->assertNotNull($request);
    }

    public function testToArray()
    {
        $request= new Post3dsRequest(123456789, 'https://www.example.com/3ds/check');

        $this->assertIsArray($request->toArray());
    }

    public function testArrayItemParameters()
    {
        $request= new Post3dsRequest(123456789, 'https://www.example.com/3ds/check');

        $this->assertEquals(123456789, Arr::get($request->toArray(), 'TransactionId'));
        $this->assertEquals('https://www.example.com/3ds/check', Arr::get($request->toArray(), 'PaRes'));
    }
}