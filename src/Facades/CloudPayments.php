<?php

namespace Nikservik\Subscriptions\Facades;

use Illuminate\Support\Facades\Facade;

class CloudPayments extends Facade 
{
    protected static function getFacadeAccessor() 
    { 
        return 'cloudpayments'; 
    }
}