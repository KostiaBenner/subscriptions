<?php

namespace Nikservik\Subscriptions\CloudPayments;

use App\User;
use Faker\Factory;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Nikservik\Subscriptions\CloudPayments\ApiResponseFactory;
use Nikservik\Subscriptions\CloudPayments\PaymentApiResponse;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;

class PaymentApiResponseFactory extends ApiResponseFactory
{
    protected static $states = [
        'successful' => [
            'Success' => true,
            'Model' => [
                'Status' => 'Completed',
            ],
        ],
        'unsuccessful' => [
            'Success' => false,
            'Model' => [
                'Status' => 'Declined',
            ],
        ],
        'testOverwrite' => [
            'Model' => [
                'Name' => 'Overwritten',
            ],
        ],
        'withTransaction' => [
            'Model' => [
                'TransactionId' => '::numberBetween',
            ],
        ],
        'withToken' => [
            'Model' => [
                'Token' => '::uuid',
            ],
        ],
        'need3dSecure' => [
            'Model' => [
                'PaReq' => '::randomNumber',
                'AcsUrl' => '::url',
            ],
        ],
    ];

    public static function make($states = [], $parameters = []): PaymentApiResponse
    {
        $faker = Factory::create();

        $response = self::applyStates($states, self::generate($faker), $faker);
        $response = self::applyParameters($parameters, $response, $faker);

        return new PaymentApiResponse($response);
    }

    public static function makeWithSubscription($states = [], Subscription $subscription): PaymentApiResponse
    {
        return self::make($states, [
            'Model' => [
                'Amount' => $subscription->price,
                'Currency' => $subscription->currency,
                'AccountId' => $subscription->user_id,
                'InvoiceId' => $subscription->id,
                'Email' => $subscription->user->email,
            ]
        ]);
    }

    public static function makeWithUserAndTariff($states = [], User $user, Tariff $tariff): PaymentApiResponse
    {
        return self::make($states, [
            'Model' => [
                'Amount' => $tariff->priceToPay,
                'Currency' => $tariff->currency,
                'AccountId' => $user->id,
            ]
        ]);
    }

    public static function makeWithUser($states = [], User $user): PaymentApiResponse
    {
        return self::make($states, [
            'Model' => [
                'AccountId' => $user->id,
            ]
        ]);
    }

    protected static function generate(Faker $faker): array
    { 
        $card = $faker->creditCardNumber;
        $status = $faker->randomElement(['Completed', 'Declined']);
        return [
            'Success' => $faker->boolean,
            'Message' => $faker->boolean ? null : $faker->sentence(),
            'Model' => [
                'TransactionId' => $faker->randomNumber,
                'Amount' => $faker->randomFloat(2, 1, 1000),
                'Currency' => $faker->currencyCode,
                'CurrencyCode' => 0,
                'InvoiceId' => $faker->numerify('#######'),
                'AccountId' => $faker->numerify('#######'),
                'Email' => $faker->email,
                'Description' => $faker->sentence(6),
                'JsonData' => null,
                'CreatedDate' => "\/Date(".$faker->unixTime."0000)\/",
                'CreatedDateIso' => $faker->iso8601, //все даты в UTC
                'TestMode' => true,
                'IpAddress' => $faker->ipv4,
                'IpCountry' => $faker->countryCode,
                'IpCity' => $faker->city,
                'IpRegion' => $faker->state,
                'IpDistrict' => $faker->state,
                'IpLatitude' => $faker->latitude,
                'IpLongitude' => $faker->longitude,
                'CardFirstSix' => Str::substr($card, 0, 6),
                'CardLastFour' => Str::substr($card, -4),
                'CardType' => $faker->creditCardType,
                'CardTypeCode' => 0,
                'Issuer' => $faker->company,
                'IssuerBankCountry' => $faker->countryCode,
                'Status' => $status,
                'StatusCode' => $faker->randomDigit,
                'Reason' => $faker->sentence(2), //причина отказа
                'ReasonCode' => $faker->randomNumber,
                'CardHolderMessage' => $faker->sentence(6), //сообщение для покупателя
                'Name' => $faker->name,
            ],
        ]; 
    }
}
