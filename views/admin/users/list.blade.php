@extends('admin.layout')

@section('content')
    <h1 class="page-header mb-6">@lang('admin/users.listTitle')</h1>
<div class="my-4 text-right">
    <a class="button small mr-4" href="/users/create">
        @lang('admin/users.create')
    </a>
</div>

<div class="text-center flex flex-col sm:flex-row justify-between mx-4">
    <div class="text-center">
        @if($list == 'all')
            <span class="selector active">@lang('admin/users.listAll') ({{ $stats['all'] }})</span>
        @else 
            <a class="selector" href="/users">@lang('admin/users.listAll') ({{ $stats['all'] }})</a>
        @endif

        @if($list == 'search')
            <span class="selector active">@lang('admin/users.listSearchResults') ({{ $stats['search'] }})</span>
        @endif
    </div>
    <div class="sm:ml-2">
        <form action="/users/search" role="search" style="white-space: nowrap;">
            <input class="py-1 px-3 w-auto border border-r-0 rounded-l-lg border-gray-400 focus:outline-none" 
                type="text" name="q" value="{{ $query ?? '' }}" placeholder="@lang('admin/users.search')" required><button type="submit" class="py-1 px-3 border rounded-r-lg border-indigo-500 bg-indigo-500 text-white">
                <svg class="fill-current text-white h-5 inline-block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path class="heroicon-ui" d="M16.32 14.9l5.39 5.4a1 1 0 0 1-1.42 1.4l-5.38-5.38a8 8 0 1 1 1.41-1.41zM10 16a6 6 0 1 0 0-12 6 6 0 0 0 0 12z"/></svg>
            </button>
        </form>
    </div>
</div>

@forelse ($users as $user)
    @include('subscriptions::admin.users.card', ['user' => $user])
@empty
    <div class="p-4 m-4 border rounded-lg border-gray-200 text-center text-gray-700">
        @lang('admin/users.listEmpty')
    </div>
@endforelse

{{ $users->links('vendor.pagination.simple') }}

@endsection
