<?php

namespace Nikservik\Subscriptions\Controllers;

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
        Route::prefix('api/cp')->namespace('Nikservik\Subscriptions\Controllers')
            ->group(function () {
            Route::post('receipt', 'CloudPaymentsController@receipt');
        });
    }

    public function __construct()
    {
        $this->middleware(['api']);
    }

    public function receipt(Request $request)
    {
        Subscriptions::saveReceipt($request->TransactionId, $request->Url);

        return ['code' => 0];
    }
}