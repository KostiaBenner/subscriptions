<?php

namespace Nikservik\Subscriptions;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TranslatableField implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        if (! $translations = json_decode($value, true))
            $translations = $value;
        return new Translatable($translations);
    }

    public function set($model, $key, $value, $attributes)
    {
        if (! $value instanceof Translatable)
            $value = new Translatable($value);
        return json_encode($value->toArray());
    }
}