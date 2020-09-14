<?php

namespace Tests\Subscriptions;

use Nikservik\Subscriptions\CloudPayments\PaymentApiResponseFactory;
use Tests\TestCase;

class PaymentApiResponseTest extends TestCase
{
    public function testGetter()
    {
        $response = PaymentApiResponseFactory::make([], ['test' => 'text']);

        $this->assertEquals('text', $response->test);
    }

    public function testIsSuccessfulWhenCompleted()
    {
        $response = PaymentApiResponseFactory::make(['successful']);

        $this->assertTrue($response->isSuccessful());
    }

    public function testIsSuccessfulWhenAuthorized()
    {
        $response = PaymentApiResponseFactory::make(['successful'], ['Model' => ['Status' => 'Authorized']]);

        $this->assertTrue($response->isSuccessful());
    }

    public function testIsSuccessfulWhenDeclined()
    {
        $response = PaymentApiResponseFactory::make(['unsuccessful']);

        $this->assertFalse($response->isSuccessful());
    }

    public function testNeed3dSecureWhenUnsuccessful()
    {
        $response = PaymentApiResponseFactory::make(['unsuccessful', 'need3dSecure']);

        $this->assertTrue($response->need3dSecure());
    }

    public function testNeed3dSecureWhenSuccessful()
    {
        $response = PaymentApiResponseFactory::make(['successful']);

        $this->assertFalse($response->need3dSecure());
    }

    public function testNeed3dSecureWithoutParameters()
    {
        $response = PaymentApiResponseFactory::make(['unsuccessful']);

        $this->assertFalse($response->need3dSecure());
    }

    public function testGetErrorMessageWithGoodCode()
    {
        $response = PaymentApiResponseFactory::make(['unsuccessful'], ['Model' => ['ReasonCode' => 5030]]);

        $this->assertEquals('errors.try_later', $response->getErrorMessage());
    }

    public function testGetErrorMessageWithBadCode()
    {
        $response = PaymentApiResponseFactory::make(['unsuccessful'], ['Model' => ['ReasonCode' => 7777]]);

        $this->assertEquals('errors.undefined', $response->getErrorMessage());
    }
}