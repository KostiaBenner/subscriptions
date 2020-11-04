<?php

namespace Tests\Subscriptions;

use Carbon\Carbon;
use Carbon\Carbonite;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Mail\SubscriptionAboutToEnd;
use Nikservik\Subscriptions\Models\Subscription;
use Tests\TestCase;

class WarnBeforeEndTest extends TestCase
{

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

    public function testWarnTrial()
    {
        $subscription = factory(Subscription::class)->states('trial', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00')]);

        Subscriptions::warnBeforeEnd();

        Mail::assertQueued(SubscriptionAboutToEnd::class);
    }

    public function testWarnCancelled()
    {
        $subscription = factory(Subscription::class)->states('paid')
            ->create(['status' => 'Cancelled', 'next_transaction_date' => Carbon::parse('2020-07-03 08:00')]);

        Subscriptions::warnBeforeEnd();

        Mail::assertQueued(SubscriptionAboutToEnd::class);
    }

    public function testWarnMass()
    {
        $subscription = factory(Subscription::class, 5)->states('trial', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00')]);

        Subscriptions::warnBeforeEnd();

        Mail::assertQueued(SubscriptionAboutToEnd::class, 5);
    }

    public function testNotWarnPaid()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00')]);

        Subscriptions::warnBeforeEnd();

        Mail::assertNotQueued(SubscriptionAboutToEnd::class);
    }

    public function testNotWarnCancelledTrial()
    {
        $subscription = factory(Subscription::class)->states('trial')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00'), 'status' => 'Cancelled']);

        Subscriptions::warnBeforeEnd();

        Mail::assertNotQueued(SubscriptionAboutToEnd::class);
    }

    public function testNotWarnEnded()
    {
        $subscription = factory(Subscription::class)->states('trial')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00'), 'status' => 'Ended']);

        Subscriptions::warnBeforeEnd();

        Mail::assertNotQueued(SubscriptionAboutToEnd::class);
    }

    public function testNotWarnBefore()
    {
        $subscription = factory(Subscription::class)->states('trial', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-02 11:59')]);

        Subscriptions::warnBeforeEnd();

        Mail::assertNotQueued(SubscriptionAboutToEnd::class);
    }

    public function testNotWarnAfter()
    {
        $subscription = factory(Subscription::class)->states('trial', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 12:01')]);

        Subscriptions::warnBeforeEnd();

        Mail::assertNotQueued(SubscriptionAboutToEnd::class);
    }
}