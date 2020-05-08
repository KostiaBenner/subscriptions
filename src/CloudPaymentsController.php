<?php

namespace Nikservik\Subscriptions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Nikservik\Subscriptions\Facades\Payments;
use Nikservik\Subscriptions\Facades\Subscriptions;

class CloudPaymentsController extends Controller
{
    public static function apiRoutes() 
    { 
        Route::post('api/cp/pay', 'Nikservik\Subscriptions\CloudPaymentsController@pay');
        Route::post('api/cp/receipt', 'Nikservik\Subscriptions\CloudPaymentsController@receipt');
    }

    public function __construct()
    {
        $this->middleware(['api']);
    }

    public function pay(Request $request)
    {
        Log::debug($request);
        
        return ['code' => 0];
    }

    public function receipt(Request $request)
    {
        Subscriptions::saveReceipt($request->TransactionId, $request->Url);

        return ['code' => 0];
    }
}