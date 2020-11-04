<?php

namespace Tests\Subscriptions;

use App\User;
use Carbon\Carbon;
use Carbon\Carbonite;
use Illuminate\Support\Facades\Mail;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Mail\SubscriptionActivated;
use Nikservik\Subscriptions\Mail\SubscriptionCancelled;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;

class SubscriptionTraitTest extends TestCase
{
    public function testToken()
    {
        $user = factory(User::class)->make();

        $user->token = 'test token';

        $this->assertEquals('test token', $user->token);
    }

    public function testCardLastFour()
    {
        $user = factory(User::class)->make();

        $user->cardLastFour = '1234';

        $this->assertEquals('1234', $user->cardLastFour);
    }

    public function testDelete()
    {
        $subscription = factory(Subscription::class)->states('active')->create();

        $subscription->user->delete();

        $this->assertEquals('Ended', $subscription->refresh()->status);
    }

    public function testHadTrial()
    {
        $subscription = factory(Subscription::class)->states('trial')->create(['status' => 'Ended']);

        $this->assertEquals(1, $subscription->user->hadTrial);
    }

    public function testHadTrialNone()
    {
        $subscription = factory(Subscription::class)->states('paid')->create();

        $this->assertEquals(0, $subscription->user->hadTrial);
    }

    public function testFeatures()
    {
        $subscription = factory(Subscription::class)->states('active')->create(['features' => ['feature 1', 'feature 2']]);

        $this->assertEquals(2, count($subscription->user->features));
        $this->assertEquals('feature 1', $subscription->user->features[0]);
    }

    public function testSubscriptionActive()
    {
        $subscription = factory(Subscription::class)->states('active')->create();
        $user = $subscription->user;

        $this->assertNotNull($user->subscription());
        $this->assertEquals($subscription->id, $user->subscription()->id);
    }

    public function testSubscriptionPastDue()
    {
        $subscription = factory(Subscription::class)->create(['status' => 'PastDue']);
        $user = $subscription->user;

        $this->assertNotNull($user->subscription());
        $this->assertEquals($subscription->id, $user->subscription()->id);
    }

    public function testSubscriptionCancelled()
    {
        Carbonite::freeze('2020-01-01');
        $subscription = factory(Subscription::class)->states('paid')->create(['status' => 'Cancelled', 'next_transaction_date' => Carbon::parse('2020-01-05')]);
        $user = $subscription->user;

        $this->assertNotNull($user->subscription());
        $this->assertEquals($subscription->id, $user->subscription()->id);
    }
}
