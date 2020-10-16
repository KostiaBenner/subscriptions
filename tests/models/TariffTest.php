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
}