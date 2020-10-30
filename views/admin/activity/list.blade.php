@extends('admin.layout')

@section('content')
    <h1 class="page-header mb-6">@lang('subscriptions::admin/activity.listTitle')</h1>

<div class="mx-4">
        @if($type == 'all')
            <span class="selector active">@lang('subscriptions::admin/activity.listAll')</span>
        @else 
            <a class="selector" href="/activity">@lang('subscriptions::admin/activity.listAll')</a>
        @endif
</div>

@forelse ($activities as $activity)
    @include('subscriptions::admin.activity.card', ['activity' => $activity])
@empty
    <div class="p-4 m-4 border rounded-lg border-gray-200 text-center text-gray-700">
        @lang('subscriptions::admin/activity.listEmpty')
    </div>
@endforelse

{{ $activities->links('vendor.pagination.simple') }}

@endsection
