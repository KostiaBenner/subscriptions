<?php

namespace Tests\Subscriptions;

use Carbon\Carbon;
use Carbon\Carbonite;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Mail\SubscriptionAboutToRenew;
use Nikservik\Subscriptions\Models\Subscription;
use Tests\TestCase;

class WarnBeforeChargeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        Config::set('subscriptions.before_charge.warn', true);
        Config::set('subscriptions.before_charge.before', '2 days');
        Carbonite::freeze('2020-07-01');
    }

    protected function tearDown(): void
    {
        Carbonite::release();
        parent::tearDown();
    }

    public function testWarn()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00')]);

        Subscriptions::warnBeforeCharge();

        Mail::assertQueued(SubscriptionAboutToRenew::class);
    }

    public function testWarnMass()
    {
        $subscription = factory(Subscription::class, 5)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00')]);

        Subscriptions::warnBeforeCharge();

        Mail::assertQueued(SubscriptionAboutToRenew::class, 5);
    }

    public function testNotWarnTrial()
    {
        $subscription = factory(Subscription::class)->states('trial', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00')]);

        Subscriptions::warnBeforeCharge();

        Mail::assertNotQueued(SubscriptionAboutToRenew::class);
    }

    public function testNotWarnCancelled()
    {
        $subscription = factory(Subscription::class)->states('trial')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00'), 'status' => 'Cancelled']);

        Subscriptions::warnBeforeCharge();

        Mail::assertNotQueued(SubscriptionAboutToRenew::class);
    }

    public function testNotWarnEnded()
    {
        $subscription = factory(Subscription::class)->states('trial')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00'), 'status' => 'Ended']);

        Subscriptions::warnBeforeCharge();

        Mail::assertNotQueued(SubscriptionAboutToRenew::class);
    }

    public function testNotWarnBefore()
    {
        $subscription = factory(Subscription::class)->states('trial', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-02 11:59')]);

        Subscriptions::warnBeforeCharge();

        Mail::assertNotQueued(SubscriptionAboutToRenew::class);
    }

    public function testNotWarnAfter()
    {
        $subscription = factory(Subscription::class)->states('trial', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 12:01')]);

        Subscriptions::warnBeforeCharge();

        Mail::assertNotQueued(SubscriptionAboutToRenew::class);
    }
}