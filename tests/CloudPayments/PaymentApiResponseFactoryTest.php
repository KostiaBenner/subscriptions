<?php

namespace Tests\Subscriptions;

use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Nikservik\Subscriptions\CloudPayments\PaymentApiResponseFactory;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;

class PaymentApiResponseFactoryTest extends TestCase
{
    public function testFactory()
    {
        $response = PaymentApiResponseFactory::make();

        $this->assertNotNull($response);
        $this->assertNotNull($response->Success);
        $this->assertNotNull($response->Model);
    }

    public function testStateDeepOverwrite()
    {
        $response = PaymentApiResponseFactory::make(['testOverwrite']);

        $this->assertEquals('Overwritten', $response->Name);
    }

    public function testSuccessfulState()
    {
        $response = PaymentApiResponseFactory::make(['successful']);

        $this->assertTrue($response->Success);
        $this->assertEquals('Completed', $response->Status);
    }

    public function testUnsuccessfulState()
    {
        $response = PaymentApiResponseFactory::make(['unsuccessful']);

        $this->assertFalse($response->Success);
        $this->assertEquals('Declined', $response->Status);
    }

    public function testWithTransactionState()
    {
        $response = PaymentApiResponseFactory::make(['withTransaction']);

        $this->assertTrue(is_int($response->TransactionId));
    }

    public function testWithTokenState()
    {
        $response = PaymentApiResponseFactory::make(['withToken']);

        $this->assertTrue(is_string($response->Token));
    }

    public function test3dSecureState()
    {
        $response = PaymentApiResponseFactory::make(['need3dSecure']);

        $this->assertNotNull($response->PaReq);
        $this->assertEquals('http', Str::substr($response->AcsUrl, 0, 4));
    }

    public function testMakeWithSubscription()
    {
        $subscription = factory(Subscription::class)->states('active', 'paid')->create();

        $response = PaymentApiResponseFactory::makeWithSubscription([], $subscription);

        $this->assertEquals($subscription->price, $response->Amount);
        $this->assertEquals($subscription->currency, $response->Currency);
        $this->assertEquals($subscription->user_id, $response->AccountId);
        $this->assertEquals($subscription->id, $response->InvoiceId);
        $this->assertEquals($subscription->user->email, $response->Email);
    }

    public function testMakeWithUserAndTariff()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();

        $response = PaymentApiResponseFactory::makeWithUserAndTariff([], $user, $tariff);

        $this->assertEquals($tariff->price, $response->Amount);
        $this->assertEquals($tariff->currency, $response->Currency);
        $this->assertEquals($user->id, $response->AccountId);
    }

    public function testMakeWithUser()
    {
        $user = factory(User::class)->create();

        $response = PaymentApiResponseFactory::makeWithUser([], $user);

        $this->assertEquals($user->id, $response->AccountId);
    }
}