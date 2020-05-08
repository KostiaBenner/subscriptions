<?php

namespace Nikservik\Subscriptions\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Nikservik\Subscriptions\Models\Payment;
use Nikservik\Subscriptions\TranslatableField;

class Subscription extends Model
{
    protected $fillable = [
        'slug', 'name', 'price', 'currency', 'period', 'prolongable', 
    ];

    protected $casts = [
        'features' => 'array',
        'availability' => 'array',
        'texts' => 'array',
        'last_transaction_date' => 'date',
        'next_transaction_date' => 'date',
        'name' => TranslatableField::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isEndless()
    {
        return $this->period == 'endless';
    }

    public function isPaid()
    {
        return $this->price > 0;
    }

    public function isTrial()
    {
        return ! $this->isEndless() and ! $this->prolongable and $this->price == 0;
    }
}
