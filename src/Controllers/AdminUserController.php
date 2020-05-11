<?php

namespace Nikservik\Subscriptions\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserCreateRequest;
use App\Http\Requests\Admin\UserEditRequest;
use App\Mail\VerifyEmail;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Nikservik\Subscriptions\Facades\Payments;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Models\Payment;
use Nikservik\Subscriptions\Models\Tariff;

class AdminUserController extends Controller
{

    static function routes()
    {
        Route::domain('admin.'.Str::after(config('app.url'),'//'))
            ->namespace('Nikservik\Subscriptions\Controllers')->prefix('users')->group(function () {
            Route::post('{user}/subscription', 'AdminUserController@subscription')->middleware('can:update,user');
            Route::get('search', 'AdminUserController@search')->middleware('can:viewAny,App\User');
            Route::get('payments/{payment}/delete', 'AdminUserController@refund')->middleware('can:delete,payment');
            Route::resource('', 'AdminUserController');
        });
    }

    public function __construct()
    {
        $this->middleware(['web', 'auth:web', 'isAdmin']);
        $this->authorizeResource(User::class, 'user');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'DESC')->simplePaginate(10);

        return view('subscriptions::admin.users.list', [
            'users' => $users, 
            'list' => 'all', 
            'stats' => $this->stats(),
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->q;
        $users = User::where('name', 'LIKE', '%'.$query.'%')
            ->orWhere('email', 'LIKE', '%'.$query.'%')
            ->orderBy('created_at', 'DESC')->paginate(10)
            ->appends(['q' => $query]);

        return view('subscriptions::admin.users.list', [
            'users' => $users, 
            'list' => 'search', 
            'query' => $query,
            'stats' => [
                'all' => User::count(),
                'search' => $users->total(),
            ],
        ]);
    }

    public function create()
    {
        return view('subscriptions::admin.users.create');
    }

    public function store(UserCreateRequest $request)
    {
        $request->merge(['password' => Hash::make($request->password)]);
        
        $user = User::create($request->all());

        if ($request->has('dontVerify')) {
            $user->email_verified_at = Carbon::now();
            $user->save();
        } else {
            Mail::to($user->email)->queue(new VerifyEmail($user));
        }
        Subscriptions::activateDefault($user);

        return redirect('/users');
    }

    public function show(User $user)
    {
        $tariffs = Tariff::where('price', 0)->get();
        return view('subscriptions::admin.users.show', ['user' => $user, 'tariffs' => $tariffs]);
    }

    public function edit(User $user)
    {
        return view('subscriptions::admin.users.edit', ['user' => $user]);
    }

    public function update(UserEditRequest $request, User $user)
    {
        $user->fill($request->except('password'));
        if ($request->password)
            $user->password = Hash::make($request->password);

        $user->save();

        return redirect('/users/'.$user->id);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect('/users');
    }

    public function refund(Payment $payment)
    {
        Payments::refund($payment);

        return redirect('/users/'.$payment->user_id);
    }

    public function subscription(Request $request, User $user)
    {
        $this->validate($request, ['tariff' => 'required|exists:tariffs,id']);

        $tariff = Tariff::findOrFail($request->tariff);

        Subscriptions::activate($user, $tariff);

        return redirect('/users/'.$user->id);
    }

    protected function stats()
    {
        return [
            'all' => User::count(),
        ];
    }
}
