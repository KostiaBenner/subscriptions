<?php

namespace Nikservik\Subscriptions\Facades;

use Illuminate\Support\Facades\Facade;

class Subscriptions extends Facade 
{
    protected static function getFacadeAccessor() 
    { 
        return 'subscriptions'; 
    }
}