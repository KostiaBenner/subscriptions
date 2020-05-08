<?php

return [
    'before_charge' => [
        'warn' => true,
        'before' => '2 days',
    ],

    'past_due' => [
        'reject' => '7 days',
    ],

    'periods' => [
        '15 days', 
        '1 month', 
        '1 year', 
        'endless', // reqiured
    ],

    'features' => [ // demo set
        'see-welcome',
        'see-sky',
        'read-books',
        'earn-money',
    ],
];