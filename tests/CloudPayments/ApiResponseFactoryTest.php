<?php

namespace Tests\Subscriptions;

use Nikservik\Subscriptions\CloudPayments\ApiResponseFactory;
use Tests\TestCase;

class ApiResponseFactoryTest extends TestCase
{
    public function testFactory()
    {
        $response = ApiResponseFactory::make();

        $this->assertNotNull($response);
        $this->assertNotNull($response->Success);
    }

    public function testSetParameter()
    {
        $response = ApiResponseFactory::make([], ['test' => 'text']);

        $this->assertEquals('text', $response->test);
    }

    public function testDeepParameter()
    {
        $response = ApiResponseFactory::make([], ['Model' => ['test' => 'text']]);

        $this->assertEquals('text', $response->test);
    }

    public function testFactoryState()
    {
        $response = ApiResponseFactory::make(['testMessage']);

        $this->assertEquals('test', $response->Message);
    }

    public function testFakedState()
    {
        $response = ApiResponseFactory::make(['withTransaction']);

        $this->assertNotNull($response->Model);
        $this->assertTrue(is_int($response->TransactionId));
    }

    public function testMergeStates()
    {
        $response = ApiResponseFactory::make(['withTransaction', 'testModel']);

        $this->assertTrue(is_int($response->TransactionId));
        $this->assertEquals('parameter', $response->TestParameter);
    }

    public function testDeepParameterWithState()
    {
        $response = ApiResponseFactory::make(['testModel'], ['Model' => ['test' => 'text']]);

        $this->assertEquals('parameter', $response->TestParameter);
        $this->assertEquals('text', $response->test);
    }

    public function testParameterOverwrite()
    {
        $response = ApiResponseFactory::make(['successful'], ['Success' => false]);

        $this->assertFalse($response->Success);
    }

    public function testDeepParameterOverwrite()
    {
        $response = ApiResponseFactory::make(['testModel'], ['Model' => ['TestParameter' => 'text']]);

        $this->assertEquals('text', $response->TestParameter);
    }
}