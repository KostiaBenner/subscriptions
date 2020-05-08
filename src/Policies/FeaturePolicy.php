<?php

namespace Nikservik\Subscriptions\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class FeaturePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function use(User $user, $feature)
    {
        return $user->subscription() 
            and $user->subscription()->features
            and in_array($feature, $user->subscription()->features);
    }
}
