<?php

namespace Tests\Subscriptions;

use Nikservik\Subscriptions\CloudPayments\ApiResponseFactory;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function testGetter()
    {
        $response = ApiResponseFactory::make([], ['test' => 'text']);

        $this->assertEquals('text', $response->test);
    }

    public function testIsSuccessful()
    {
        $response = ApiResponseFactory::make(['successful']);

        $this->assertTrue($response->isSuccessful());
    }

    public function testNullErrorMessageOnSuccess()
    {
        $response = ApiResponseFactory::make(['successful']);

        $this->assertNull($response->getErrorMessage());
    }

    public function testGetErrorMessageOnError()
    {
        $response = ApiResponseFactory::make(['unsuccessful']);

        $this->assertEquals('errors.failed', $response->getErrorMessage());
    }
}