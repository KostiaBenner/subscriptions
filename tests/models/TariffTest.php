<?php

namespace Tests\Subscriptions;

use Nikservik\Subscriptions\Models\Tariff;
use Tests\TestCase;

class TariffTest extends TestCase
{
    
    public function testEmptyFeatures()
    {
        $tariff = factory(Tariff::class)->make(['features' => null]);

        $this->assertIsArray($tariff->features);
    }

    public function testGetDefault()
    {
        $tariff = factory(Tariff::class)->states('defaultFree')->make();

        $this->assertTrue($tariff->default);
    }

    public function testSetDefault()
    {
        $tariff = factory(Tariff::class)->make();
        $tariff->default = true;

        $this->assertTrue($tariff->default);
    }

    public function testGetVisible()
    {
        $tariff = factory(Tariff::class)->make();

        $this->assertFalse($tariff->visible);
    }

    public function testSetVisible()
    {
        $tariff = factory(Tariff::class)->make();
        $tariff->visible = true;

        $this->assertTrue($tariff->visible);
    }

    public function testGetTypeFree()
    {
        $tariff = factory(Tariff::class)->states('defaultFree')->make();

        $this->assertEquals('free', $tariff->type);
    }

    public function testGetTypePaidPeriodic()
    {
        $tariff = factory(Tariff::class)->states('periodic')->make();

        $this->assertEquals('paid', $tariff->type);
    }

    public function testGetTypePaidLifetime()
    {
        $tariff = factory(Tariff::class)->states('lifetime')->make();

        $this->assertEquals('paid', $tariff->type);
    }

    public function testGetTypeTrial()
    {
        $tariff = factory(Tariff::class)->states('trial')->make();

        $this->assertEquals('trial', $tariff->type);
    }

    public function testCrossedPrice()
    {
        $tariff = factory(Tariff::class)->create(['crossedPrice' => 230]);

        $this->assertEquals(230, $tariff->refresh()->crossedPrice);
    }

    public function testCrossedPriceToArray()
    {
        $tariff = factory(Tariff::class)->make(['crossedPrice' => 230]);

        $this->assertArrayHasKey('crossedPrice', $tariff->toArray());
    }

    public function testSavings()
    {
        $tariff = factory(Tariff::class)->states('periodic')->make(['price' => 300, 'crossedPrice' => 1000]);

        $this->assertEquals(700, $tariff->savings);
    }

    public function testSavingsWithoutCrossedPrice()
    {
        $tariff = factory(Tariff::class)->states('periodic')->make(['price' => 300]);

        $this->assertNull($tariff->savings);
    }

    public function testDescription()
    {
        $tariff = factory(Tariff::class)->create(['description' => 'test description']);

        $this->assertEquals('test description', $tariff->refresh()->description);
    }

    public function testDescriptionToArray()
    {
        $tariff = factory(Tariff::class)->make(['description' => 'test description']);

        $this->assertArrayHasKey('description', $tariff->toArray());
    }
}