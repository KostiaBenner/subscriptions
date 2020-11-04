<?php

namespace Nikservik\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\TranslatableField;

class Tariff extends Model
{
    protected $fillable = [
        'slug', 'name', 'price', 'crossedPrice', 'currency', 'period', 'prolongable', 'description',
    ];

    protected $casts = [
        'features' => 'array',
        'availability' => 'array',
        'texts' => 'array',
        'name' => TranslatableField::class,
        'description' => TranslatableField::class,
    ];

    protected $appends = ['type', 'visible', 'default', 'crossedPrice', 'priceToPay'];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFeaturesAttribute($value)
    {
        return is_null($value) ? [] : json_decode($value, true);
    }

    public function getDefaultAttribute() 
    {
        return Arr::get($this->availability, 'default', false);
    }   

    public function setDefaultAttribute($default) 
    {
        $availability = $this->availability;
        $availability['default'] = (boolean) $default;
        $this->availability = $availability;
    }   
    
    public function getVisibleAttribute() 
    {
        return Arr::get($this->availability, 'visible', false);
    }   

    public function setVisibleAttribute($visible) 
    {
        $availability = $this->availability;
        $availability['visible'] = (boolean) $visible;
        $this->availability = $availability;
    }   
    
    public function getCrossedPriceAttribute() 
    {
        return Arr::get($this->texts, 'crossedPrice', null);
    }   

    public function setCrossedPriceAttribute($crossedPrice) 
    {
        $texts = $this->texts;
        $texts['crossedPrice'] = $crossedPrice ? (float) $crossedPrice : null;
        $this->texts = $texts;
    }   
    
    public function getSavingsAttribute() 
    {
        if ($this->crossedPrice === null)
            return null;

        return $this->crossedPrice - $this->price;
    }   

    public function getTypeAttribute()
    {
        if ($this->price > 0)
            return 'paid';

        if (! $this->prolongable and $this->period != 'endless')
            return 'trial';

        return 'free';
    }

    public function getPriceToPayAttribute()
    {
        if (!Auth::check() || $this->price == 0) 
            return $this->price;
        
        $subscription = Auth::user()->subscription();
        if ($subscription->price == 0)
            return $this->price;

        if ($subscription->period == 'endless') {
            if ($this->period == 'endless')
                return ($this->price - $subscription->price) > 0 ? $this->price - $subscription->price : 0;
            else
                return $this->price;
        }

        $paidPeriod = $subscription->next_transaction_date->timestamp - $subscription->last_transaction_date->timestamp;
        $paidPeriodLeft = $subscription->next_transaction_date->timestamp - Carbon::now()->timestamp;

        $toPay = round($this->price - $paidPeriodLeft / $paidPeriod * $subscription->price, 2);
        if ($toPay < 0)
            $toPay = 0;

        return $toPay;
    }

    public function toCopy()
    {
        return array_merge($this->toArray(), ['name' => $this->name, 'description' => $this->description]);
    }
}