<?php

namespace Tests\Subscriptions;

use App\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;

class SetDefaultSubscriptionTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();        
        Tariff::where('id', '>', 0)->delete();
        $default = factory(Tariff::class)->states('defaultFree')->create();
    }

    public function testSetDefault()
    {
        $user = factory(User::class)->create();

        event(new Registered($user));

        $this->assertEquals(Subscriptions::defaultTariff()->id, $user->subscription()->tariff_id);
    }
}