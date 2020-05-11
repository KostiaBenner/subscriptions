@extends('admin.layout')

@section('content')
<h1 class="page-header">
    <a href="/tariffs" class="text-white">@lang('admin/tariffs.listTitle')</a> 
</h1>
<h2 class="sub-header">{{ $tariff->name }} ({{ $tariff->slug }})</h2>

<div class="mb-4 text-right">
    <a class="button small mr-4" href="/tariffs/{{ $tariff->id }}/edit">
        @lang('admin/tariffs.modify')
    </a>
    <a class="button small" href="javascript:document.tariff_delete.submit()" onclick="return confirm('@lang('admin/tariffs.confirmDelete')')">
        @lang('admin/tariffs.delete')
    </a>
</div>
    <form name="tariff_delete" action="/tariffs/{{ $tariff->id }}" method="POST">
        @csrf 
        @method('DELETE')
    </form>

<p class="my-4 mx-10">{{ $tariff->price }} {{ $tariff->currency }}
    / @lang('subscriptions::periods.'.$tariff->period)
    </p>
@if($tariff->prolongable)
    <p class="my-4 mx-10">@lang('admin/tariffs.prolongable')</p>
@endif
    <p class="my-4 mx-10">@lang('admin/tariffs.'.($tariff->visible ? 'visible' : 'invisible'))</p>

<h2 class="sub-title">@lang('admin/tariffs.features')</h2>

<p class="my-4 mx-10">
    @foreach((is_array($tariff->features)?$tariff->features:[]) as $feature)
        @lang('subscriptions::features.'.$feature)<br>
    @endforeach
</p>




@endsection