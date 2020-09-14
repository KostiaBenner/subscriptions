<?php

namespace Tests\Subscriptions;

use App\User;
use Carbon\Carbonite;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Mail\SubscriptionActivated;
use Nikservik\Subscriptions\Mail\SubscriptionCancelled;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;

class SubscriptionsManagerFreeTest extends TestCase
{
    use DatabaseTransactions;

    public function testActivate()
    {
        $tariff = factory(Tariff::class)->states('free')->create();
        $user = factory(User::class)->create();

        Subscriptions::activate($user, $tariff);

        $this->assertNotNull($user->subscription());
        $this->assertEquals('Active', $user->subscription()->status);
    }

    public function testActivateTariffIdAndFeatures()
    {
        $tariff = factory(Tariff::class)->states('free')->create();
        $user = factory(User::class)->create();

        Subscriptions::activate($user, $tariff);

        $this->assertEquals($tariff->id, $user->subscription()->tariff_id);
        $this->assertEquals($tariff->features, $user->subscription()->features);
    }

    public function testActivateNextTransactionTrial()
    {
        Carbonite::freeze('2020-01-01');
        $tariff = factory(Tariff::class)->states('trial')->create(['period' => '1 month']);
        $user = factory(User::class)->create();

        Subscriptions::activate($user, $tariff);

        $this->assertEquals('2020-02-01', $user->subscription()->next_transaction_date->format('Y-m-d'));
        Carbonite::release();
    }

    public function testActivateNextTransactionEndless()
    {
        $tariff = factory(Tariff::class)->states('free')->create(['period' => 'endless']);
        $user = factory(User::class)->create();

        Subscriptions::activate($user, $tariff);

        $this->assertNull($user->subscription()->next_transaction_date);
    }

    public function testActivateUpdateIdAndFeatures()
    {
        Mail::fake();
        $newTariff = factory(Tariff::class)->states('free')->create();
        $previousSubscription = factory(Subscription::class)->states('active')->create();
        $user = $previousSubscription->user;

        Subscriptions::activate($user, $newTariff);

        $this->assertEquals($newTariff->id, $user->subscription()->tariff_id);
        $this->assertEquals($newTariff->features, $user->subscription()->features);
    }

    public function testActivateEndPrevious()
    {
        Mail::fake();
        $newTariff = factory(Tariff::class)->states('free')->create();
        $previousSubscription = factory(Subscription::class)->states('active')->create();
        $user = $previousSubscription->user;

        Subscriptions::activate($user, $newTariff);

        $this->assertEquals('Ended', $previousSubscription->refresh()->status);
        Mail::assertQueued(SubscriptionActivated::class);
    }

    public function testActivateFirstNoMail()
    {
        Mail::fake();
        $tariff = factory(Tariff::class)->states('free')->create();
        $user = factory(User::class)->create();

        Subscriptions::activate($user, $tariff);

        Mail::assertNothingQueued();
    }

    public function testCancelEnded()
    {
        Mail::fake();
        $subscription = factory(Subscription::class)->states('active')->create(['price' => 0]);

        Subscriptions::cancel($subscription);

        $this->assertEquals('Ended', $subscription->refresh()->status);
    }

    public function testCancelDefaultActivated()
    {
        Mail::fake();
        $subscription = factory(Subscription::class)->states('active')->create(['price' => 0]);
        $user = $subscription->user;

        Subscriptions::cancel($subscription);

        $this->assertEquals(Subscriptions::defaultTariff()->id, $user->subscription()->tariff_id);
    }
    public function testCancelMailSent()
    {
        Mail::fake();
        $subscription = factory(Subscription::class)->states('active')->create(['price' => 0]);

        Subscriptions::cancel($subscription);

        Mail::assertQueued(SubscriptionCancelled::class);
    }
}
