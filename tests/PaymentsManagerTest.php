<?php

namespace Tests\Subscriptions;

use App\User;
use Carbon\Carbon;
use Carbon\Carbonite;
use Illuminate\Support\Arr;
use Nikservik\Subscriptions\CloudPayments\ApiResponseFactory;
use Nikservik\Subscriptions\CloudPayments\PaymentApiResponse;
use Nikservik\Subscriptions\CloudPayments\PaymentApiResponseFactory;
use Nikservik\Subscriptions\Facades\CloudPayments;
use Nikservik\Subscriptions\Facades\Payments;
use Nikservik\Subscriptions\Models\Payment;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;

class PaymentsManagerTest extends TestCase
{

    public function testCharge()
    {
        $subscription = factory(Subscription::class)->states('active', 'paid', 'userWithToken')->create();
        CloudPayments::shouldReceive('paymentsTokensCharge')
            ->andReturn(PaymentApiResponseFactory::makeWithSubscription(['successful', 'withToken', 'withTransaction'], $subscription));

        $result = Payments::charge($subscription);

        $this->assertTrue((boolean) $result);
        $this->assertEquals(1, $subscription->payments()->count());
    }

    public function testChargeDeclined()
    {
        $subscription = factory(Subscription::class)->states('active', 'paid', 'userWithToken')->create();
        CloudPayments::shouldReceive('paymentsTokensCharge')
            ->andReturn(PaymentApiResponseFactory::makeWithSubscription(['unsuccessful', 'withToken', 'withTransaction'], $subscription));

        $result = Payments::charge($subscription);

        $this->assertFalse($result);
        $this->assertEquals(0, $subscription->payments()->count());
    }

    public function testChargeByCrypt()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsCharge')
            ->andReturn(PaymentApiResponseFactory::makeWithUserAndTariff(['successful', 'withToken', 'withTransaction'], $user, $tariff));

        $result = Payments::chargeByCrypt($user, $tariff, 'Cardholder', '127.0.0.1', '123456787654321');

        $this->assertTrue((boolean) $result);
        $this->assertEquals(1, $user->subscription()->payments()->count());
    }

    public function testChargeByCryptWithPreviousSubscription()
    {
        Carbonite::freeze('2020-01-01');
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['period' => '1 month', 'price' => 200, 'last_transaction_date' => Carbon::parse('2019-12-17'), 'next_transaction_date' => Carbon::parse('2020-01-16')]);
        $user = $subscription->user;
        $this->actingAs($user);
        $tariff = factory(Tariff::class)->states('periodic')->create(['period' => '1 year', 'price' => 2000]);
        CloudPayments::shouldReceive('paymentsCardsCharge')
            ->andReturn(PaymentApiResponseFactory::makeWithUserAndTariff(['successful', 'withToken', 'withTransaction'], $user, $tariff));

        $result = Payments::chargeByCrypt($user, $tariff, 'Cardholder', '127.0.0.1', '123456787654321');

        $this->assertTrue((boolean) $result);
        $this->assertEquals(1900, $user->subscription()->payments()->first()->amount);
    }

    public function testChargeByCryptDeclined()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsCharge')
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction']));

        $result = Payments::chargeByCrypt($user, $tariff, 'Cardholder', '127.0.0.1', '123456787654321');

        $this->assertIsString($result);
        $this->assertNull($user->subscription());
    }

    public function testChargeByCryptNeed3dSecure()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsCharge')
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction', 'need3dSecure']));

        $result = Payments::chargeByCrypt($user, $tariff, 'Cardholder', '127.0.0.1', '123456787654321');

        $this->assertTrue(is_array($result));
        $this->assertTrue(Arr::has($result, ['TransactionId', 'PaReq', 'AcsUrl']));
    }

    public function testPost3ds()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsPost3ds')
            ->andReturn(PaymentApiResponseFactory::makeWithUserAndTariff(['successful', 'withToken', 'withTransaction'], $user, $tariff));

        $result = Payments::post3ds($user, $tariff, 1234, 'test Pa Res');

        $this->assertTrue((boolean) $result);
        $this->assertEquals(1, $user->subscription()->payments()->count());
    }

    public function testPost3dsDeclined()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsPost3ds')
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction']));

        $result = Payments::post3ds($user, $tariff, 1234, 'test Pa Res');

        $this->assertIsString($result);
        $this->assertNull($user->subscription());
    }

    public function testAuthorizeByCrypt()
    {
        $user = factory(User::class)->create();
        CloudPayments::shouldReceive([ 
            'paymentsCardsAuth' => PaymentApiResponseFactory::makeWithUser(['successful', 'withToken', 'withTransaction'], $user),
            'paymentsVoid' => ApiResponseFactory::make(['successful']),
        ]);

        $result = Payments::authorizeByCrypt($user, 'Cardholder', '127.0.0.1', '123456787654321');

        $this->assertTrue((boolean) $result);
        $this->assertNotNull($user->token);
    }

    public function testAuthorizeByCryptDeclined()
    {
        $user = factory(User::class)->create();
        CloudPayments::shouldReceive('paymentsCardsAuth')
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction']));

        $result = Payments::authorizeByCrypt($user, 'Cardholder', '127.0.0.1', '123456787654321');

        $this->assertIsString($result);
        $this->assertEquals('', $user->token);
    }

    public function testAuthorizeByCryptNeed3dSecure()
    {
        $user = factory(User::class)->create();
        CloudPayments::shouldReceive('paymentsCardsAuth')
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction', 'need3dSecure']));

        $result = Payments::authorizeByCrypt($user, 'Cardholder', '127.0.0.1', '123456787654321');

        $this->assertTrue(is_array($result));
        $this->assertTrue(Arr::has($result, ['TransactionId', 'PaReq', 'AcsUrl']));
    }

    public function testAuthorizePost3ds()
    {
        $user = factory(User::class)->create();
        CloudPayments::shouldReceive([ 
            'paymentsCardsPost3ds' => PaymentApiResponseFactory::makeWithUser(['successful', 'withToken', 'withTransaction'], $user),
            'paymentsVoid' => ApiResponseFactory::make(['successful']),
        ]);

        $result = Payments::authorizePost3ds($user, 1234, 'test Pa Res');

        $this->assertTrue((boolean) $result);
        $this->assertNotNull($user->token);
    }

    public function testAuthorizePost3dsDeclined()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsPost3ds')
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction']));

        $result = Payments::authorizePost3ds($user, 1234, 'test Pa Res');

        $this->assertIsString($result);
        $this->assertEquals('', $user->token);
    }

    public function testRefund()
    {
        $payment = factory(Payment::class)->create();
        CloudPayments::shouldReceive('paymentsRefund')
            ->andReturn(ApiResponseFactory::make(['successful']));

        $result = Payments::refund($payment);

        $this->assertTrue($result);
        $this->assertEquals('Refunded', $payment->status);        
    }

    public function testRefundDeclined()
    {
        $payment = factory(Payment::class)->create();
        CloudPayments::shouldReceive('paymentsRefund')
            ->andReturn(ApiResponseFactory::make(['unsuccessful']));

        $result = Payments::refund($payment);

        $this->assertFalse($result);
        $this->assertEquals('Completed', $payment->status);        
    }
}