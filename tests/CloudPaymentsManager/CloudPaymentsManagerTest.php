<?php

namespace Tests\Subscriptions;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Nikservik\Subscriptions\CloudPayments\ApiResponse;
use Nikservik\Subscriptions\CloudPayments\CardChargeRequest;
use Nikservik\Subscriptions\CloudPayments\CloudPaymentsManager;
use Nikservik\Subscriptions\CloudPayments\PaymentApiResponse;
use Nikservik\Subscriptions\CloudPayments\Post3dsRequest;
use Nikservik\Subscriptions\CloudPayments\TokenChargeRequest;
use Tests\TestCase;

class CloudPaymentsManagerTest extends TestCase
{
    use DatabaseTransactions;
    
    public function testCreate()
    {
        $manager = new CloudPaymentsManager;

        $this->assertNotNull($manager);
    }

    public function testApiTestCall()
    {
        Config::set('cloudpayments.apiUrl', 'https://api.example.test');
        Http::fake(['*' => Http::response('[]', 200, ['Headers']),]);

        (new CloudPaymentsManager)->apiTest();

        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.example.test/test';
        });        
    }

    public function testApiAuthorisation()
    {
        Config::set('cloudpayments', ['apiUrl' => 'https://api.example.test', 'publicId' => 'user', 'apiSecret' => 'secret']);
        Http::fake(['*' => Http::response('[]', 200, ['Headers']),]);

        (new CloudPaymentsManager)->apiTest();

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Basic ' . base64_encode('user:secret'));
        });        
    }

    public function testPaymentsCardsCharge()
    {
        Config::set('cloudpayments.apiUrl', 'https://api.example.test');
        Http::fake(['*' => Http::response('{"Success":false,"Message":"Error message"}', 200, ['Headers']),]);
        
        $response = (new CloudPaymentsManager)->paymentsCardsCharge(
            new CardChargeRequest(12.0, 'RUB', 'Card Holder', 'crypt packet', '127.0.0.1', 1234, 'test@example.com', 'item')
        );

        $this->assertInstanceOf(PaymentApiResponse::class, $response);
        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.example.test/payments/cards/charge';
        });      
    }

    public function testPaymentsCardsAuth()
    {
        Config::set('cloudpayments.apiUrl', 'https://api.example.test');
        Http::fake(['*' => Http::response('{"Success":false,"Message":"Error message"}', 200, ['Headers']),]);
        
        $response = (new CloudPaymentsManager)->paymentsCardsAuth(
            new CardChargeRequest(12.0, 'RUB', 'Card Holder', 'crypt packet', '127.0.0.1', 1234, 'test@example.com', 'item')
        );

        $this->assertInstanceOf(PaymentApiResponse::class, $response);
        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.example.test/payments/cards/auth';
        });      
    }

    public function testPaymentsCardsPost3ds()
    {
        Config::set('cloudpayments.apiUrl', 'https://api.example.test');
        Http::fake(['*' => Http::response('{"Success":false,"Message":"Error message"}', 200, ['Headers']),]);
        
        $response = (new CloudPaymentsManager)->paymentsCardsPost3ds(
            new Post3dsRequest(123456789, 'https://www.example.com/3ds/check')
        );

        $this->assertInstanceOf(PaymentApiResponse::class, $response);
        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.example.test/payments/cards/post3ds';
        });      
    }

    public function testPaymentsTokensCharge()
    {
        Config::set('cloudpayments.apiUrl', 'https://api.example.test');
        Http::fake(['*' => Http::response('{"Success":false,"Message":"Error message"}', 200, ['Headers']),]);
        
        $response = (new CloudPaymentsManager)->paymentsTokensCharge(
            new TokenChargeRequest(12.0, 'RUB', 'user token', 1234, 'test@example.com', 'item')
        );

        $this->assertInstanceOf(PaymentApiResponse::class, $response);
        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.example.test/payments/tokens/charge';
        });      
    }

    public function testPaymentsVoid()
    {
        Config::set('cloudpayments.apiUrl', 'https://api.example.test');
        Http::fake(['*' => Http::response('{"Success":false,"Message":"Error message"}', 200, ['Headers']),]);
        
        $response = (new CloudPaymentsManager)->paymentsVoid(123456789);

        $this->assertInstanceOf(ApiResponse::class, $response);
        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.example.test/payments/void'
                && $request['json'] == ['TransactionId' => 123456789];
        });      
    }

    public function testPaymentsRefund()
    {
        Config::set('cloudpayments.apiUrl', 'https://api.example.test');
        Http::fake(['*' => Http::response('{"Success":false,"Message":"Error message"}', 200, ['Headers']),]);
        
        $response = (new CloudPaymentsManager)->paymentsRefund(123456789, 12.0);

        $this->assertInstanceOf(ApiResponse::class, $response);
        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.example.test/payments/refund'
                && $request['json'] == ['TransactionId' => 123456789, 'Amount' => 12.0];
        });      
    }
}