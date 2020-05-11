@extends('admin.layout')

@section('content')
    <h1 class="page-header mb-6">@lang('admin/tariffs.listTitle')</h1>

<div class="mt-8 text-right">
    <a class="button small mr-4" href="/tariffs/create">
        @lang('admin/tariffs.create')
    </a>
</div>

@forelse ($tariffs as $tariff)
    @include('subscriptions::admin.tariffs.card', ['tariff' => $tariff])
@empty
    <div class="p-4 m-4 border rounded-lg border-gray-200 text-center text-gray-700">
        @lang('admin/tariffs.listEmpty')
    </div>
@endforelse

@endsection
