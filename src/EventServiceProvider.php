<?php

namespace Nikservik\Subscriptions;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Nikservik\Subscriptions\Listeners\SetDefaultSubscription;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SetDefaultSubscription::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}
