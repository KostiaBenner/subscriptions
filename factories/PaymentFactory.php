<?php

use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Nikservik\Subscriptions\Models\Payment;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;

$factory->define(Payment::class, function (Faker $faker) {
    return [
        'subscription_id' => factory(Subscription::class),
        'user_id' => factory(User::class),
        'remote_transaction_id' => $faker->randomNumber,
        'card_last_digits' => Str::substr($faker->creditCardNumber, -4),
        'amount' => $faker->randomFloat(2, 0, 1000),
        'currency' => $faker->currencyCode,
        'status' => 'Completed',
    ];
});
