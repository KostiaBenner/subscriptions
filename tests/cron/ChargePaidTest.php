<?php

namespace Tests\Subscriptions;

use Carbon\Carbon;
use Carbon\Carbonite;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Nikservik\Subscriptions\Facades\Payments;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Mail\SubscriptionPastDue;
use Nikservik\Subscriptions\Mail\SubscriptionRejected;
use Nikservik\Subscriptions\Mail\SubscriptionRenewed;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;

class ChargePaidTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();        
        Config::set('subscriptions.past_due.reject', '3 days');
        Carbonite::freeze('2020-07-05');
        Tariff::where('id', '>', 0)->delete();
        Subscription::where('id', '>', 0)->delete();
        $default = factory(Tariff::class)->states('defaultFree')->create();
    }

    protected function tearDown(): void
    {
        Carbonite::release();
        parent::tearDown();
    }

    public function testCharge()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-04 08:00:00'), 'period' => '1 month']);
        Payments::shouldReceive('charge')->once()->andReturn(true);

        Subscriptions::chargePaid();

        $this->assertEquals('2020-08-04 08:00:00', $subscription->refresh()->next_transaction_date->format('Y-m-d H:i:s'));
    }

    public function testChargeMailSent()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-04 08:00:00'), 'period' => '1 month']);
        Payments::shouldReceive('charge')->once()->andReturn(true);

        Subscriptions::chargePaid();

        Mail::assertQueued(SubscriptionRenewed::class);
    }

    public function testNotChargeCancelled()
    {
        $subscription = factory(Subscription::class)->states('paid')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-04 08:00:00'), 'period' => '1 month', 'status' => 'Cancelled']);
        Payments::shouldReceive()->shouldNotReceive('charge');

        Subscriptions::chargePaid();

        Mail::assertNotQueued(SubscriptionRenewed::class);
    }

    public function testNotChargeEnded()
    {
        $subscription = factory(Subscription::class)->states('paid')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-04 08:00:00'), 'period' => '1 month', 'status' => 'Ended']);
        Payments::shouldReceive()->shouldNotReceive('charge');

        Subscriptions::chargePaid();

        Mail::assertNotQueued(SubscriptionRenewed::class);
    }

    public function testNotChargeRejected()
    {
        $subscription = factory(Subscription::class)->states('paid')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-04 08:00:00'), 'period' => '1 month', 'status' => 'Rejected']);
        Payments::shouldReceive()->shouldNotReceive('charge');

        Subscriptions::chargePaid();

        Mail::assertNotQueued(SubscriptionRenewed::class);
    }

    public function testNotChargeAwaiting()
    {
        $subscription = factory(Subscription::class)->states('paid')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-04 08:00:00'), 'period' => '1 month', 'status' => 'Awaiting']);
        Payments::shouldReceive()->shouldNotReceive('charge');

        Subscriptions::chargePaid();

        Mail::assertNotQueued(SubscriptionRenewed::class);
    }

    public function testNotChargeBefore()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-05 08:00:00'), 'period' => '1 month']);
        Payments::shouldReceive()->shouldNotReceive('charge');

        Subscriptions::chargePaid();

        Mail::assertNotQueued(SubscriptionRenewed::class);
    }

    public function testPastDueIfFailed()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-04 08:00:00'), 'period' => '1 month']);
        Payments::shouldReceive('charge')->once()->andReturn(false);

        Subscriptions::chargePaid();

        $this->assertEquals('PastDue', $subscription->refresh()->status);
    }

    public function testPastDueMailSent()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-04 08:00:00'), 'period' => '1 month']);
        Payments::shouldReceive('charge')->once()->andReturn(false);

        Subscriptions::chargePaid();

        Mail::assertQueued(SubscriptionPastDue::class);
    }

    public function testPastDueSecondTime()
    {
        $subscription = factory(Subscription::class)->states('paid')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-03 08:00:00'), 'period' => '1 month', 'status' => 'PastDue']);
        Payments::shouldReceive('charge')->once()->andReturn(false);

        Subscriptions::chargePaid();

        $this->assertEquals('PastDue', $subscription->refresh()->status);
    }

    public function testRejectedIfFailedAndOutdated()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-01 08:00:00'), 'period' => '1 month', 'status' => 'PastDue']);
        Payments::shouldReceive('charge')->once()->andReturn(false);

        Subscriptions::chargePaid();

        $this->assertEquals('Rejected', $subscription->refresh()->status);
    }

    public function testRejectedMailSent()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-01 08:00:00'), 'period' => '1 month', 'status' => 'PastDue']);
        Payments::shouldReceive('charge')->once()->andReturn(false);

        Subscriptions::chargePaid();

        Mail::assertQueued(SubscriptionRejected::class);
    }

    public function testRejectedDefaultActivated()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-01 08:00:00'), 'period' => '1 month', 'status' => 'PastDue']);
        $user = $subscription->user;
        Payments::shouldReceive('charge')->once()->andReturn(false);

        Subscriptions::chargePaid();

        $this->assertEquals(Subscriptions::defaultTariff()->id, $user->subscription()->tariff_id);
    }

    public function testRejectedActive()
    {
        $subscription = factory(Subscription::class)->states('paid', 'active')
            ->create(['next_transaction_date' => Carbon::parse('2020-07-01 08:00:00'), 'period' => '1 month', 'status' => 'Active']);
        Payments::shouldReceive('charge')->once()->andReturn(false);

        Subscriptions::chargePaid();

        $this->assertEquals('Rejected', $subscription->refresh()->status);
    }
}