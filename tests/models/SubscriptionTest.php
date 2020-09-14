<?php

namespace Tests\Subscriptions;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Nikservik\Subscriptions\Models\Subscription;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use DatabaseTransactions;
    
    public function testEmptyFeatures()
    {
        $subscription = factory(Subscription::class)->make(['features' => null]);

        $this->assertIsArray($subscription->features);
    }

    public function testIsEndless()
    {
        $subscription = factory(Subscription::class)->make(['period' => 'endless']);

        $this->assertTrue($subscription->isEndless());
    }

    public function testIsEndlessPeriodic()
    {
        $subscription = factory(Subscription::class)->make(['period' => '1 month']);

        $this->assertFalse($subscription->isEndless());
    }

    public function testIsPaid()
    {
        $subscription = factory(Subscription::class)->make(['price' => 12.0]);

        $this->assertTrue($subscription->isPaid());
    }

    public function testIsPaidFree()
    {
        $subscription = factory(Subscription::class)->make(['price' => 0]);

        $this->assertFalse($subscription->isPaid());
    }

    public function testIsTrial()
    {
        $subscription = factory(Subscription::class)->make(['price' => 0, 'period' => '1 month', 'prolongable' => false]);

        $this->assertTrue($subscription->isTrial());
    }

    public function testIsTrialPaid()
    {
        $subscription = factory(Subscription::class)->make(['price' => 12.0]);

        $this->assertFalse($subscription->isTrial());
    }

    public function testIsTrialPeriodic()
    {
        $subscription = factory(Subscription::class)->make(['prolongable' => true]);

        $this->assertFalse($subscription->isTrial());
    }

    public function testIsTrialEndless()
    {
        $subscription = factory(Subscription::class)->make(['period' => 'endless']);

        $this->assertFalse($subscription->isTrial());
    }
}