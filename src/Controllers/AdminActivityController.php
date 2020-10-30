<?php

namespace Nikservik\Subscriptions\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TariffCreateRequest;
use App\Http\Requests\Admin\TariffEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Nikservik\Subscriptions\Models\Tariff;
use Spatie\Activitylog\Models\Activity;

class AdminActivityController extends Controller
{

    static function routes()
    {
        Route::domain('admin.'.Str::after(config('app.url'),'//'))
            ->namespace('Nikservik\Subscriptions\Controllers')
            ->group(function () {
            Route::get('activity/{type?}', 'AdminActivityController@index');
        });
    }

    public function __construct()
    {
        $this->middleware(['web', 'auth:web', 'isAdmin']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type = null)
    {
        $activities = Activity::orderBy('id', 'DESC')->paginate(20);
;
        return view('subscriptions::admin.activity.list', ['activities' => $activities, 'type' => 'all']);
    }
}
