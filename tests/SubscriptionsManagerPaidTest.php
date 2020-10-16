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

class SubscriptionsManagerPaidTest extends TestCase
{

    public function testActivate()
    {
        $tariff = factory(Tariff::class)->states('periodic')->create();
        $user = factory(User::class)->create();

        Subscriptions::activate($user, $tariff);

        $this->assertNull($user->subscription());
        $this->assertEquals('Awaiting', $user->subscriptions[0]->status);
    }

    public function testActivateForce()
    {
        $tariff = factory(Tariff::class)->states('periodic')->create();
        $user = factory(User::class)->create();

        Subscriptions::activate($user, $tariff, true);

        $this->assertNotNull($user->subscription());
        $this->assertEquals('Active', $user->subscription()->status);
    }

    public function testActivateTariffIdAndFeatures()
    {
        $tariff = factory(Tariff::class)->states('periodic')->create();
        $user = factory(User::class)->create();

        Subscriptions::activate($user, $tariff, true);

        $this->assertEquals($tariff->id, $user->subscription()->tariff_id);
        $this->assertEquals($tariff->features, $user->subscription()->features);
    }

    public function testActivateNextTransaction()
    {
        Carbonite::freeze('2020-01-01');
        $tariff = factory(Tariff::class)->states('periodic')->create();
        $user = factory(User::class)->create();

        Subscriptions::activate($user, $tariff);

        $this->assertEquals('2020-02-01', $user->subscriptions[0]->next_transaction_date->format('Y-m-d'));
        Carbonite::release();
    }

    public function testConfirmActivation()
    {
        $subscription = factory(Subscription::class)->states('paid')->create(['status' => 'Awaiting']);
        $user = $subscription->user;

        Subscriptions::confirmActivation($subscription);

        $this->assertNotNull($user->subscription());
        $this->assertEquals('Active', $user->subscription()->status);
    }

    public function testConfirmActivationMailSent()
    {
        $subscription = factory(Subscription::class)->states('paid')->create(['status' => 'Awaiting']);

        Subscriptions::confirmActivation($subscription);

        Mail::assertQueued(SubscriptionActivated::class);
    }

    public function testConfirmActivationLastTransaction()
    {
        Carbonite::freeze('2020-01-01');
        $subscription = factory(Subscription::class)->states('paid')->create(['status' => 'Awaiting']);

        Subscriptions::confirmActivation($subscription);

        $this->assertEquals('2020-01-01', $subscription->last_transaction_date->format('Y-m-d'));
        Carbonite::release();
    }

    public function testConfirmActivationUpdateIdAndFeatures()
    {
        $previousSubscription = factory(Subscription::class)->states('active')->create();
        $user = $previousSubscription->user;
        $subscription = factory(Subscription::class)->states('paid')->create(['user_id' => $user->id, 'status' => 'Awaiting']);

        Subscriptions::confirmActivation($subscription);

        $this->assertEquals($subscription->id, $user->subscription()->id);
        $this->assertEquals($subscription->features, $user->subscription()->features);
    }

    public function testConfirmActivationEndPrevious()
    {
        $previousSubscription = factory(Subscription::class)->states('active')->create();
        $user = $previousSubscription->user;
        $subscription = factory(Subscription::class)->states('paid')->create(['user_id' => $user->id, 'status' => 'Awaiting']);

        Subscriptions::confirmActivation($subscription);

        $this->assertEquals('Ended', $previousSubscription->refresh()->status);
    }

    public function testNeedActivation()
    {
        $subscription = factory(Subscription::class)->states('paid')->create(['status' => 'Awaiting']);

        $this->assertTrue(Subscriptions::needActivation($subscription));
    }

    public function testNeedActivationActive()
    {
        $subscription = factory(Subscription::class)->states('paid')->create(['status' => 'Active']);

        $this->assertFalse(Subscriptions::needActivation($subscription));
    }

    public function testNeedActivationEnded()
    {
        $subscription = factory(Subscription::class)->states('paid')->create(['status' => 'Ended']);

        $this->assertFalse(Subscriptions::needActivation($subscription));
    }

    public function testCancelPeriodic()
    {
        $subscription = factory(Subscription::class)->states('active', 'paid')
            ->create(['period' => '1 month', 'next_transaction_date' => Carbon::tomorrow()]);

        Subscriptions::cancel($subscription);

        $this->assertEquals('Cancelled', $subscription->refresh()->status);
    }

    public function testCancelEndless()
    {
        $subscription = factory(Subscription::class)->states('active', 'paid')
            ->create(['period' => 'endless']);

        Subscriptions::cancel($subscription);

        $this->assertEquals('Ended', $subscription->refresh()->status);
    }

    public function testCancelEndlessdefaultActivated()
    {
        $subscription = factory(Subscription::class)->states('active', 'paid')
            ->create(['period' => 'endless']);
        $user = $subscription->user;

        Subscriptions::cancel($subscription);

        $this->assertEquals(Subscriptions::defaultTariff()->id, $user->subscription()->tariff_id);
    }

    public function testCancelMailSent()
    {
        $subscription = factory(Subscription::class)->states('active', 'paid')->create();

        Subscriptions::cancel($subscription);

        Mail::assertQueued(SubscriptionCancelled::class);
    }
}