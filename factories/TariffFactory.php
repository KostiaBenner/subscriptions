<?php

use Faker\Generator as Faker;
use Nikservik\Subscriptions\Models\Tariff;

$factory->define(Tariff::class, function (Faker $faker) {
    return [
        'slug' => $faker->city, 
        'name' => $faker->country,
        'price' => $faker->randomFloat(2, 0, 1000), 
        'currency' => 'RUB', 
        'period' => '1 month',
        'prolongable' => $faker->boolean,
    ];
});
