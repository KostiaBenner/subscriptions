<?php

use App\User;
use Faker\Generator as Faker;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;

$factory->define(Subscription::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'tariff_id' => factory(Tariff::class),
        'slug' => function (array $subscription) { return Tariff::find($subscription['tariff_id'])->slug; },
        'name' => function (array $subscription) { return Tariff::find($subscription['tariff_id'])->name; },
        'price' => function (array $subscription) { return Tariff::find($subscription['tariff_id'])->price; }, 
        'currency' => function (array $subscription) { return Tariff::find($subscription['tariff_id'])->currency; }, 
        'period' => function (array $subscription) { return Tariff::find($subscription['tariff_id'])->period; },
        'prolongable' => function (array $subscription) { return Tariff::find($subscription['tariff_id'])->prolongable; },
        'features' => function (array $subscription) { return Tariff::find($subscription['tariff_id'])->features; },
        'texts' => function (array $subscription) { return Tariff::find($subscription['tariff_id'])->texts; },
        'status' => $faker->randomElement(['Active', 'Awaiting', 'Cancelled', 'Ended', 'Rejected', 'PastDue']),
    ];
});

$factory->state(Subscription::class, 'active', [
    'status' => 'Active',
]);

$factory->state(Subscription::class, 'paid', function (Faker $faker) {
    return [
        'tariff_id' => factory(Tariff::class)->states('periodic'),
    ];
});

$factory->state(Subscription::class, 'userWithToken', function (Faker $faker) {
    return [
        'user_id' => factory(User::class)->states('withToken'),
    ];
});

$factory->state(Subscription::class, 'trial', function (Faker $faker) {
    return [
        'slug' => 'trial'.$faker->randomDigit,
        'name' => 'Trial '.$faker->word,
        'price' => 0,
        'period' => '1 month',
        'prolongable' => false,
    ];
});
