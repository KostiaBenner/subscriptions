<?php

namespace Nikservik\Subscriptions\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Nikservik\Subscriptions\Facades\Payments;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Models\Tariff;
use Nikservik\Subscriptions\Requests\ActivateSubscriptionRequest;
use Nikservik\Subscriptions\Requests\AuthorizeByCryptogramRequest;
use Nikservik\Subscriptions\Requests\PayByCryptogramRequest;
use Nikservik\Subscriptions\Requests\Post3dsRequest;

class SubscriptionController extends Controller
{
    public static function apiRoutes() 
    { 
        Route::prefix('api/subscriptions')->namespace('Nikservik\Subscriptions\Controllers')
            ->group(function () {
            Route::get('', 'SubscriptionController@index');
            Route::get('translations', 'SubscriptionController@translations');
            Route::get('payments', 'SubscriptionController@payments')->middleware('auth:api');
            Route::post('', 'SubscriptionController@activateFree')->middleware('auth:api');
            Route::post('cancel', 'SubscriptionController@cancel')->middleware('auth:api');
            Route::post('crypt', 'SubscriptionController@crypt')->middleware('auth:api');
            Route::post('authorize', 'SubscriptionController@authorizeCrypt')->middleware('auth:api');
            Route::post('{user}', 'SubscriptionController@autorizePost3ds');
            Route::post('{user}/{tariff}', 'SubscriptionController@post3ds');
        });
    }

    public function __construct()
    {
        $this->middleware(['api']);
    }

    public function index()
    {
        return [
            'status' => 'success',
            'data' => [
                'subscriptions' => Subscriptions::list(),
            ] 
        ];
    }

    public function translations()
    {
        return [
            'status' => 'success',
            'data' => [
                'features' => Subscriptions::features(),
                'periods' => Subscriptions::periods(),
            ] 
        ];
    }

    public function payments()
    {
        return [
            'status' => 'success',
            'data' => [
                'payments' => Auth::user()->payments()->orderBy('created_at', 'desc')->get(),
            ] 
        ];
    }

    public function activateFree(ActivateSubscriptionRequest $request)
    {
        $tariff = Tariff::findOrFail($request->tariff);
        if ($tariff->price != 0)
            return ['status' => 'error'];

        $result = Subscriptions::activate(Auth::user(), Tariff::find($request->tariff));

        if (! $result)
            return ['status' => 'error'];

        return [
            'status' => 'success',
            'data' => [
                'subscription' => $result
            ]
        ];
    }

    public function cancel()
    {
        if (! Auth::user()->subscription())
            return ['status' => 'error'];
        
        $subscription = Subscriptions::cancel(Auth::user()->subscription());

        if (! $subscription)
            return ['status' => 'error'];

        return [
            'status' => 'success',
            'data' => [
                'subscription' => $subscription
            ]
        ];
    }

    public function crypt(PayByCryptogramRequest $request)
    {
        $tariff = Tariff::find($request->tariff);
        $result = Payments::chargeByCrypt(Auth::user(), $tariff, $request->name, $request->ip(), $request->crypt);

        if (is_string($result))
            return [ 'status' => 'error', 'message' => $result];

        if (is_array($result))
            return [ 'status' => 'need3ds', 'data' => $result];

        return [ 'status' => 'success', 'data' => [ 'subscription' => $result ] ];
    }

    public function authorizeCrypt(AuthorizeByCryptogramRequest $request)
    {
        $result = Payments::authorizeByCrypt(Auth::user(), $request->name, $request->ip(), $request->crypt);

        if ($result === true)
            return [ 'status' => 'success' ];

        if (is_string($result))
            return [ 'status' => 'error', 'message' => $result];

        if (is_array($result))
            return [ 'status' => 'need3ds', 'data' => $result];
    }

    public function post3ds(Post3dsRequest $request, User $user, Tariff $tariff)
    {
        $result = Payments::post3ds($user, $tariff, $request->MD, $request->PaRes);

        $error = is_string($result) ? $result : false; 
        return view('subscriptions::frame', [ 'error' => $error ]);
    }

    public function autorizePost3ds(Post3dsRequest $request, User $user)
    {
        $result = Payments::authorizePost3ds($user, $request->MD, $request->PaRes);

        $error = is_string($result) ? $result : false; 
        return view('subscriptions::frame', [ 'error' => $error ]);
    }
}