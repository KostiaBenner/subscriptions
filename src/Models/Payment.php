<?php

namespace Nikservik\Subscriptions\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Nikservik\Subscriptions\Models\Subscription;

class Payment extends Model
{
    protected $fillable = [
        'subscription_id', 'user_id', 'remote_transaction_id', 
        'card_last_digits', 'amount', 'currency', 'status', 'receipt_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
