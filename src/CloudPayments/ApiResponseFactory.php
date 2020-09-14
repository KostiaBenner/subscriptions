<?php

namespace Nikservik\Subscriptions\CloudPayments;

use Faker\Factory;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiResponseFactory
{
    protected static $states = [
        'successful' => [
            'Success' => true,
        ],
        'unsuccessful' => [
            'Success' => false,
        ],
        'testMessage' => [
            'Message' => 'test',
        ],
        'testModel' => [
            'Model' => [
                'TestParameter' => 'parameter',
            ],
        ],
        'withTransaction' => [
            'Model' => [
                'TransactionId' => '::numberBetween',
            ],
        ],
    ];

    public static function make($states = [], $parameters = []): ApiResponse
    {
        $faker = Factory::create();

        $response = self::applyStates($states, self::generate($faker), $faker);
        $response = self::applyParameters($parameters, $response, $faker);

        return new ApiResponse($response);
    }

    protected static function generate(Faker $faker): array
    { 
        return [
            'Success' => $faker->boolean,
            'Message' => $faker->boolean ? null : $faker->sentence(),
        ]; 
    }

    protected static function applyStates(array $states, array $data, Faker $faker): array
    {
        foreach ($states as $state) {
            $data = self::applyState($state, $data, $faker);
        }

        return $data;
    }

    protected static function applyState(string $state, array $data, Faker $faker): array
    {
        if (! array_key_exists($state, static::$states)) 
            return $data;

        foreach (static::$states[$state] as $name => $value) {
            $data[$name] = self::fakeAttribute(
                $value, 
                array_key_exists($name, $data) ? $data[$name] : [],
                $faker
            );
        }

        return $data;
    }

    protected static function applyParameters(array $parameters, array $data, Faker $faker): array
    {
        foreach ($parameters as $name => $value) {
            $data[$name] = self::fakeAttribute(
                $value, 
                array_key_exists($name, $data) ? $data[$name] : [],
                $faker
            );
        }

        return $data;
    }

    protected static function fakeAttribute($value, $data = [], Faker $faker)
    {
        if (! is_array($value)) {
            if (Str::substr($value, 0, 2) != '::') 
                return $value;

            $method = Str::substr($value, 2);
            return $faker->$method;
        }

        foreach ($value as $key => $subvalue) {
            $value[$key] = self::fakeAttribute($subvalue, [], $faker );
        }

        return array_merge($data, $value);
    }
}