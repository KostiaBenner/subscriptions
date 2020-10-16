<?php

namespace Tests\Subscriptions;

use App\User;
use Illuminate\Support\Facades\Config;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Models\Payment;
use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;

class SubscriptionsManagerTest extends TestCase
{

    public function testListEmpty()
    {
        Tariff::where('id', '>', 0)->delete();

        $list = Subscriptions::list();

        $this->assertEquals(0, $list->count());
    }

    public function testList()
    {
        Tariff::where('id', '>', 0)->delete();
        factory(Tariff::class, 5)->create();

        $list = Subscriptions::list();

        $this->assertEquals(5, $list->count());
    }

    public function testDefaultTariff()
    {
        Tariff::where('id', '>', 0)->delete();
        $default = factory(Tariff::class)->states('defaultFree')->create();
        factory(Tariff::class, 10)->create();

        $this->assertEquals($default->id, Subscriptions::defaultTariff()->id);
    }

    public function testActivateDefault()
    {
        Tariff::where('id', '>', 0)->delete();
        $default = factory(Tariff::class)->states('defaultFree')->create();
        factory(Tariff::class, 10)->create();
        $user = factory(User::class)->create();

        Subscriptions::activateDefault($user);

        $this->assertEquals($default->id, $user->subscription()->tariff_id);
    }

    public function testFeaturesLocales()
    {
        Config::set('app.locales', ['ru', 'en']);
        Config::set('subscriptions.features', ['feature1', 'feature2']);

        $translations = Subscriptions::features();

        $this->assertEquals(2, count($translations['feature1']));
        $this->assertIsString($translations['feature1']['ru']);
        $this->assertIsString($translations['feature1']['en']);
    }

    public function testPeriodsLocales()
    {
        Config::set('app.locales', ['ru', 'en']);
        Config::set('subscriptions.periods', ['1 month', 'endless']);

        $translations = Subscriptions::periods();

        $this->assertEquals(2, count($translations['1 month']));
        $this->assertIsString($translations['1 month']['ru']);
        $this->assertIsString($translations['1 month']['en']);
    }

    public function testSaveReceipt()
    {
        $payment = factory(Payment::class)->create();

        Subscriptions::saveReceipt($payment->remote_transaction_id, 'http://www.example.com/123456');

        $this->assertEquals('http://www.example.com/123456', $payment->refresh()->receipt_url);
    }

    public function testSaveReceiptFailed()
    {
        $payment = factory(Payment::class)->create();

        Subscriptions::saveReceipt(99999, 'http://www.example.com/123456');

        $this->assertNotEquals('http://www.example.com/123456', $payment->refresh()->receipt_url);
    }
}