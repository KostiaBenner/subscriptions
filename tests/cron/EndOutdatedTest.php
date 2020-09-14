<?php

namespace Tests\Subscriptions;

use Carbon\Carbon;
use Carbon\Carbonite;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Mail\SubscriptionEnded;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;

class EndOutdatedTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        Carbonite::freeze('2020-07-01');
        Tariff::where('id', '>', 0)->delete();
        Subscription::where('id', '>', 0)->delete();
        $default = factory(Tariff::class)->states('defaultFree')->create();
    }

    protected function tearDown(): void
    {
        Carbonite::release();

        parent::tearDown();
    }

    public function testEnd()
    {
        $subscription = factory(Subscription::class)->states('trial', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-06-30 08:00')]);

        Subscriptions::endOutdated();

        $this->assertEquals('Ended', $subscription->refresh()->status);
    }

    public function testEndMailSent()
    {
        $subscription = factory(Subscription::class)->states('trial', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-06-30 08:00')]);

        Subscriptions::endOutdated();

        Mail::assertQueued(SubscriptionEnded::class);
    }

    public function testEndDefaultActivated()
    {
        $subscription = factory(Subscription::class)->states('trial', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-06-30 08:00')]);
        $user = $subscription->user;

        Subscriptions::endOutdated();

        $this->assertEquals(Subscriptions::defaultTariff()->id, $user->subscription()->tariff_id);
    }

    public function testNotEndEnded()
    {
        $subscription = factory(Subscription::class)->states('trial')
            ->create(['next_transaction_date' => Carbon::parse('2020-06-30 08:00'), 'status' => 'Ended']);

        Subscriptions::endOutdated();

        Mail::assertNotQueued(SubscriptionEnded::class);
    }

    public function testNotEndPastDue()
    {
        $subscription = factory(Subscription::class)->states('paid')
            ->create(['next_transaction_date' => Carbon::parse('2020-06-30 08:00'), 'status' => 'PastDue']);

        Subscriptions::endOutdated();

        Mail::assertNotQueued(SubscriptionEnded::class);
    }

    public function testNotEndRejected()
    {
        $subscription = factory(Subscription::class)->states('paid')
            ->create(['next_transaction_date' => Carbon::parse('2020-06-30 08:00'), 'status' => 'Rejected']);

        Subscriptions::endOutdated();

        Mail::assertNotQueued(SubscriptionEnded::class);
    }

}