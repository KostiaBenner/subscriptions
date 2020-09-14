<?php

namespace Tests\Subscriptions;

use App\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Nikservik\Subscriptions\CloudPayments\ApiResponseFactory;
use Nikservik\Subscriptions\CloudPayments\PaymentApiResponseFactory;
use Nikservik\Subscriptions\Facades\CloudPayments;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Models\Payment;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SubscriptionControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();        
        Mail::fake();
        Tariff::where('id', '>', 0)->delete();
        $default = factory(Tariff::class)->states('defaultFree')->create();
    }

    public function testIndex()
    {
        $user = factory(User::class)->create();
        factory(Tariff::class, 5)->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->getJson('api/subscriptions')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(6, 'data.subscriptions');
    }

    public function testTranslations()
    {
        Config::set('subscriptions.features', ['feature1', 'feature2']);
        Config::set('subscriptions.periods', ['1 month', 'endless']);

        $response = $this->getJson('api/subscriptions/translations')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data.features')
            ->assertJsonCount(2, 'data.periods');
    }


    public function testPayments()
    {
        $user = factory(User::class)->create();
        $user->payments()->createMany(factory(Payment::class, 3)->make()->toArray());

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->getJson('api/subscriptions/payments')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(3, 'data.payments');
    }

    public function testActivate()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('free')->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->postJson('api/subscriptions', ['tariff' => $tariff->id])
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.subscription.tariff_id', $tariff->id);
    }

    public function testActivatePaidFail()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->postJson('api/subscriptions', ['tariff' => $tariff->id])
            ->assertStatus(200)
            ->assertJsonPath('status', 'error');
    }

    public function testActivateUnxistentFails()
    {
        $user = factory(User::class)->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->postJson('api/subscriptions', ['tariff' => 99999999])
            ->assertStatus(422);
    }

    public function testCancelFree()
    {
        $subscription = factory(Subscription::class)->states('active')->create(['price' => 0]);

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($subscription->user)])
            ->postJson('api/subscriptions/cancel')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.subscription.tariff_id', Subscriptions::defaultTariff()->id);
    }

    public function testCancelUnexistentFails()
    {
        $user = factory(User::class)->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->postJson('api/subscriptions/cancel')
            ->assertStatus(200)
            ->assertJsonPath('status', 'error');
    }

    public function testCancelTrial()
    {
        $subscription = factory(Subscription::class)->states('active', 'trial')->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($subscription->user)])
            ->postJson('api/subscriptions/cancel')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.subscription.tariff_id', Subscriptions::defaultTariff()->id);
    }

    public function testCancelPaid()
    {
        $subscription = factory(Subscription::class)->states('active', 'paid')->create(['next_transaction_date' => Carbon::tomorrow()]);

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($subscription->user)])
            ->postJson('api/subscriptions/cancel')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.subscription.id', $subscription->id)
            ->assertJsonPath('data.subscription.status', 'Cancelled');
    }

    public function testCancelPaidEndless()
    {
        $subscription = factory(Subscription::class)->states('active', 'paid')->create(['period' => 'endless']);

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($subscription->user)])
            ->postJson('api/subscriptions/cancel')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.subscription.tariff_id', Subscriptions::defaultTariff()->id);
    }

    public function testCrypt()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsCharge')->once()
            ->andReturn(PaymentApiResponseFactory::makeWithUserAndTariff(['successful', 'withToken', 'withTransaction'], $user, $tariff));

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->postJson('api/subscriptions/crypt', ['tariff' => $tariff->id, 'name' => $user->name, 'crypt' => 'test crypt'])
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.subscription.tariff_id', $tariff->id)
            ->assertJsonPath('data.subscription.status', 'Active');
    }

    public function testCryptDeclined()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsCharge')->once()
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction']));

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->postJson('api/subscriptions/crypt', ['tariff' => $tariff->id, 'name' => $user->name, 'crypt' => 'test crypt'])
            ->assertStatus(200)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('data.subscription', null);
    }

    public function testCryptNeed3ds()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsCharge')->once()
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction', 'need3dSecure']));

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->postJson('api/subscriptions/crypt', ['tariff' => $tariff->id, 'name' => $user->name, 'crypt' => 'test crypt'])
            ->assertStatus(200)
            ->assertJsonPath('status', 'need3ds');

        $this->assertNotNull(Arr::get($response, 'data.PaReq'));
        $this->assertNotNull(Arr::get($response, 'data.AcsUrl'));
        $this->assertNotNull(Arr::get($response, 'data.TransactionId'));
    }

    public function testAuthorizeCrypt()
    {
        $user = factory(User::class)->create();
        CloudPayments::shouldReceive([ 
            'paymentsCardsAuth' => PaymentApiResponseFactory::makeWithUser(['successful', 'withToken', 'withTransaction'], $user),
            'paymentsVoid' => ApiResponseFactory::make(['successful']),
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->postJson('api/subscriptions/authorize', ['name' => $user->name, 'crypt' => 'test crypt'])
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertNotEquals('', $user->refresh()->token);
    }

    public function testAuthorizeCryptDeclined()
    {
        $user = factory(User::class)->create();
        CloudPayments::shouldReceive('paymentsCardsAuth')->once()
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction']));

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->postJson('api/subscriptions/authorize', ['name' => $user->name, 'crypt' => 'test crypt'])
            ->assertStatus(200)
            ->assertJsonPath('status', 'error');

        $this->assertEquals('', $user->refresh()->token);
    }

    public function testAuthorizeCryptNeed3ds()
    {
        $user = factory(User::class)->create();
        CloudPayments::shouldReceive('paymentsCardsAuth')->once()
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction', 'need3dSecure']));

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->postJson('api/subscriptions/authorize', ['name' => $user->name, 'crypt' => 'test crypt'])
            ->assertStatus(200)
            ->assertJsonPath('status', 'need3ds');

        $this->assertNotNull(Arr::get($response, 'data.PaReq'));
        $this->assertNotNull(Arr::get($response, 'data.AcsUrl'));
        $this->assertNotNull(Arr::get($response, 'data.TransactionId'));
    }

    public function testPost3ds()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsPost3ds')
            ->andReturn(PaymentApiResponseFactory::makeWithUserAndTariff(['successful', 'withToken', 'withTransaction'], $user, $tariff));

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->post("api/subscriptions/{$user->id}/{$tariff->id}", ['MD' => '12341234', 'PaRes' => '43214321'])
            ->assertStatus(200);

        $this->assertFalse($response['error']);
        $this->assertNotNull($user->subscription());
        $this->assertEquals($tariff->id, $user->subscription()->tariff_id);
    }

    public function testPost3dsDeclined()
    {
        $user = factory(User::class)->create();
        $tariff = factory(Tariff::class)->states('periodic')->create();
        CloudPayments::shouldReceive('paymentsCardsPost3ds')
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction']));

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->post("api/subscriptions/{$user->id}/{$tariff->id}", ['MD' => '12341234', 'PaRes' => '43214321'])
            ->assertStatus(200);

        $this->assertIsString($response['error']);
        $this->assertNull($user->subscription());
    }

    public function testAuthorizePost3ds()
    {
        $user = factory(User::class)->create();
        CloudPayments::shouldReceive([ 
            'paymentsCardsPost3ds' => PaymentApiResponseFactory::makeWithUser(['successful', 'withToken', 'withTransaction'], $user),
            'paymentsVoid' => ApiResponseFactory::make(['successful']),
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->post("api/subscriptions/{$user->id}", ['MD' => '12341234', 'PaRes' => '43214321'])
            ->assertStatus(200);

        $this->assertFalse($response['error']);
        $this->assertNotEquals('', $user->refresh()->token);
    }

    public function testAuthorizePost3dsDeclined()
    {
        $user = factory(User::class)->create();
        CloudPayments::shouldReceive('paymentsCardsPost3ds')
            ->andReturn(PaymentApiResponseFactory::make(['unsuccessful', 'withTransaction']));

        $response = $this->withHeaders(['Authorization' => 'Bearer '.JWTAuth::fromUser($user)])
            ->post("api/subscriptions/{$user->id}", ['MD' => '12341234', 'PaRes' => '43214321'])
            ->assertStatus(200);

        $this->assertIsString($response['error']);
        $this->assertEquals('', $user->refresh()->token);
    }
}