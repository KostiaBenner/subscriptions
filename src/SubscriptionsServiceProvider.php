<?php

namespace Nikservik\Subscriptions;

use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Nikservik\Subscriptions\CloudPayments\CloudPaymentsManager;
use Nikservik\Subscriptions\EventServiceProvider;
use Nikservik\Subscriptions\Jobs\ChargePaid;
use Nikservik\Subscriptions\Jobs\EndCancelled;
use Nikservik\Subscriptions\Jobs\EndOutdated;
use Nikservik\Subscriptions\Jobs\WarnBeforeCharge;
use Nikservik\Subscriptions\Jobs\WarnBeforeEnd;
use Nikservik\Subscriptions\Models\Feature;
use Nikservik\Subscriptions\Models\Payment;
use Nikservik\Subscriptions\Models\Tariff;
use Nikservik\Subscriptions\PaymentsManager;
use Nikservik\Subscriptions\Policies\FeaturePolicy;
use Nikservik\Subscriptions\Policies\PaymentPolicy;
use Nikservik\Subscriptions\Policies\TariffPolicy;
use Nikservik\Subscriptions\SubscriptionsManager;

class SubscriptionsServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Tariff::class => TariffPolicy::class,
        Feature::class => FeaturePolicy::class,
        Payment::class => PaymentPolicy::class,
    ];
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->bind('subscriptions',function() {
            return new SubscriptionsManager;
        });
        $this->app->bind('payments',function() {
            return new PaymentsManager;
        });
        $this->app->bind('cloudpayments',function() {
            return new CloudPaymentsManager;
        });
        $this->mergeConfigFrom(__DIR__.'/../config/subscriptions.php', 'subscriptions');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadFactoriesFrom(__DIR__.'/../factories');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'subscriptions');
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->loadViewsFrom(__DIR__.'/../views', 'subscriptions');
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
        $this->registerPolicies();
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->job(new ChargePaid)->hourlyAt(30);
            $schedule->job(new WarnBeforeCharge)->dailyAt('1:00');
            $schedule->job(new WarnBeforeEnd)->dailyAt('2:00');
            $schedule->job(new EndCancelled)->dailyAt('3:00');
            $schedule->job(new EndOutdated)->dailyAt('4:00');
        });
        $this->publishes([
            __DIR__.'/../config/subscriptions.php' => config_path('subscriptions.php')
        ], 'config');
        $this->publishes([
            __DIR__.'/../migrations/' => database_path('migrations')
        ], 'migrations');
        $this->publishes([
            __DIR__.'/../views' => resource_path('views/vendor/subscriptions'),
        ], 'views');
        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/subscriptions'),
        ], 'translations');
    }
}
