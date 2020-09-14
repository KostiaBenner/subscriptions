<?php

namespace Tests\Subscriptions;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;

class SubscriptionTraitTest extends TestCase
{
    use DatabaseTransactions;

    public function testSubscriptionActive()
    {
        $subscription = factory(Subscription::class)->states('active')->create();

        $user = $subscription->user;

        $this->assertNotNull($user->subscription());
    }

    public function testSubscriptionCancelled()
    {
        $subscription = factory(Subscription::class)->create(['status' => 'Cancelled', 'next_transaction_date' => Carbon::tomorrow()]);

        $user = $subscription->user;

        $this->assertNotNull($user->subscription());
    }

    public function testSubscriptionEnded()
    {
        $subscription = factory(Subscription::class)->create(['status' => 'Ended']);

        $user = $subscription->user;

        $this->assertNull($user->subscription());
    }

    public function testSubscriptionAttribute()
    {
        $subscription = factory(Subscription::class)->states('active')->create();

        $user = $subscription->user;

        $this->assertIsArray($user->subscription);
    }

    public function testEmptyFeatures()
    {
        $subscription = factory(Subscription::class)->create(['features' => null]);

        $user = $subscription->user;

        $this->assertIsArray($user->features);
    }

    public function testFeatures()
    {
        $subscription = factory(Subscription::class)->states('active')->create(['features' => ['feature1', 'feature2']]);

        $user = $subscription->user;

        $this->assertIsArray($user->features);
        $this->assertGreaterThan(0, count($user->features));
    }

    public function testHadTrial()
    {
        $subscription = factory(Subscription::class)->states('trial')->create(['status' => 'Ended']);

        $user = $subscription->user;

        $this->assertTrue($user->hadTrial);
    }

    public function testGetToken()
    {
        $user = factory(User::class)->state('withToken')->make();

        $this->assertIsString($user->token);
    }

    public function testSetToken()
    {
        $user = factory(User::class)->make();
        $user->token = 'user token';

        $this->assertEquals('user token', $user->token);
    }

    public function testSetGetCardLastFour()
    {
        $user = factory(User::class)->make();
        $user->cardLastFour = '1234';

        $this->assertEquals('1234', $user->cardLastFour);
    }
}