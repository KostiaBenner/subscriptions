<?php

use Faker\Generator as Faker;
use Nikservik\Subscriptions\Models\Tariff;

$factory->define(Tariff::class, function (Faker $faker) {
    return [
        'slug' => $faker->word, 
        'name' => $faker->words(2, true),
        'price' => $faker->randomFloat(2, 0, 1000), 
        'currency' => 'RUB', 
        'period' => $faker->randomElement(
            config('subscriptions.periods')
        ),
        'prolongable' => $faker->boolean,
        'features' => $faker->randomElements(
            config('subscriptions.features'), 
            $faker->numberBetween(1, count(config('subscriptions.features')))
        ),
    ];
});

$factory->state(Tariff::class, 'free', function (Faker $faker) {
    return [
        'slug' => 'free'.$faker->randomDigit,
        'name' => 'Free '.$faker->word,
        'price' => 0,
        'period' => 'endless',
        'prolongable' => false,
    ];
});

$factory->state(Tariff::class, 'defaultFree', function (Faker $faker) {
    return [
        'slug' => 'default'.$faker->randomDigit,
        'name' => 'Free '.$faker->word,
        'price' => 0,
        'period' => 'endless',
        'prolongable' => false,
        'default' => true,
   ];
});

$factory->state(Tariff::class, 'periodic', function (Faker $faker) {
    return [
        'slug' => 'paid'.$faker->randomDigit,
        'name' => 'Monthly '.$faker->word,
        'price' => $faker->randomFloat(2, 1, 1000),
        'period' => '1 month',
        'prolongable' => true,
    ];
});

$factory->state(Tariff::class, 'trial', function (Faker $faker) {
    return [
        'slug' => 'trial'.$faker->randomDigit,
        'name' => 'Trial '.$faker->word,
        'price' => 0,
        'period' => '1 month',
        'prolongable' => false,
    ];
});

$factory->state(Tariff::class, 'lifetime', function (Faker $faker) {
    return [
        'slug' => 'lifetime'.$faker->randomDigit,
        'name' => 'Lifetime '.$faker->word,
        'price' => $faker->randomFloat(2, 100, 10000),
        'period' => 'endless',
        'prolongable' => false,
    ];
});
