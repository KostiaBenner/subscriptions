@extends('admin.layout')

@section('content')
<h1 class="page-header">
	<a href="/users" class="text-white">@lang('admin/users.listTitle')</a> 
</h1>
<h2 class="sub-header">{{ $user->name }}</h2>
<div class="mb-4 text-right">
    <a class="button small mr-4" href="/users/{{ $user->id }}/edit">
        @lang('admin/users.modify')
    </a>
    <a class="button small red" href="javascript:document.user_delete.submit()" onclick="return confirm('@lang('admin/users.confirmDelete')')">
        @lang('admin/users.delete')
    </a>
</div>
    <form name="user_delete" action="/users/{{ $user->id }}" method="POST">
        @csrf 
        @method('DELETE')
    </form>

<div class="flex items-center mt-8 mb-4 mx-10">
    @if($user->hasVerifiedEmail())
        <div class="rounded inline-block px-3 py-1 bg-indigo-400 text-white">
            @lang('admin/users.verificationDone')
        </div>
    @else
        <div class="rounded inline-block px-3 py-1 bg-indigo-300 text-white">
            @lang('admin/users.verificationSent')
        </div>
    @endif
    <div class="px-3">{{ $user->email }} </div>
</div> 
<p class="my-4 mx-10">@lang('admin/users.registered') {{ $user->created_at->format('d.m.Y') }}</p>

<p class="my-4 mx-10">@lang('admin/users.role'): @lang('app.role'.$user->role)</p>


<h2 class="sub-title">@lang('admin/users.subscription')</h2>

@if($user->subscription())
    <p class="my-4 mx-10 font-bold">@lang('admin/users.tariff') {{ $user->subscription()->name }}</p>

    <p class="my-2 mx-10">
        @foreach($user->subscription()->features as $feature)
            - @lang('subscriptions::features.'.$feature)<br>
        @endforeach
    </p>
@endif

<form autocomplete="off" method="post" action="/users/{{ $user->id }}/subscription">
    @csrf
    <div class="flex items-end">
        <div class="form-group flex-grow mt-8 @error('tariff') has-error @enderror">
            <label for="tariff">@lang('admin/users.changeTariff')</label>
            <select name="tariff" class="block">
                @foreach($tariffs as $tariff)
                    <option value="{{ $tariff->id }}" @if(old('tariff')==$tariff->id)selected=""@endif>{{ $tariff->name }}</option>
                @endforeach
            </select>
            @error('tariff')
                <div class="error-description">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="form-group text-center">
            <button type="submit" class="button">@lang('admin/users.save')</button>
        </div>
    </div>
</form>

<h2 class="sub-title">@lang('admin/users.payments')</h2>

<table class="table-auto w-full mt-8 mb-4">
    <thead>
        <tr>
            <th>@lang('admin/users.date')</th>
            <th>@lang('admin/users.card')</th>
            <th>@lang('admin/users.amount')</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($user->payments()->orderBy('created_at', 'desc')->get() as $payment)
            <tr class="text-center odd:bg-gray-200">
                <td class="py-4">{{ $payment->created_at->format("d.m.Y") }}</td>
                <td>{{ $payment->card_last_digits }}</td>
                <td>{{ $payment->amount }} {{ $payment->currency }}</td>
                <td>
                    @if($payment->status == 'Completed')
                        <a class="button small light" href="/users/payments/{{ $payment->id }}/delete" onclick="return confirm('@lang('admin/users.confirmRefund')')">
                            @lang('admin/users.refund')
                        </a>
                    @else
                        @if($payment->status == 'Authorized')
                            @lang('admin/users.Authorized')
                        @endif
                        @if($payment->status == 'Refunded')
                            @lang('admin/users.Refunded')
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection