@extends('admin.layout')

@section('content')
<h1 class="page-header">
    <a href="/tariffs" class="text-white">@lang('subscriptions::admin/tariffs.listTitle')</a> 
</h1>
<h2 class="sub-header"><a href="/tariffs/{{ $tariff->id }}">{{ $tariff->name }} ({{ $tariff->slug }})</a></h2>

<form autocomplete="off" method="post" action="/tariffs/{{ $tariff->id }}">
    @csrf
    @method('PATCH')

    @foreach(config('app.locales') as $locale)
        <div class="form-group @error('name.'.$locale) has-error @enderror">
            <label for="name[{{ $locale }}]">@lang('subscriptions::admin/tariffs.name') ({{ $locale }})</label>
            <input type="text" name="name[{{ $locale }}]" value="{{ old('name.'.$locale)?old('name.'.$locale):$tariff->name->$locale }}" placeholder="" required>
            @error('name.'.$locale)
                <div class="error-description">
                    @lang('subscriptions::admin/tariffs.'.$message)
                </div>
            @enderror
        </div>
    @endforeach
    <div class="flex">
        <div class="form-group w-1/2 @error('slug') has-error @enderror">
            <label for="slug">@lang('subscriptions::admin/tariffs.slug')</label>
            <input type="text" name="slug" value="{{ old('slug')?old('slug'):$tariff->slug }}" placeholder="" required>
            @error('slug')
                <div class="error-description">
                    @lang('subscriptions::admin/tariffs.'.$message)
                </div>
            @enderror
        </div>
        <div class="form-group w-1/2 @error('price') has-error @enderror">
            <label for="price">@lang('subscriptions::admin/tariffs.price')</label>
            <div class="flex">
                <input type="text" name="price" value="{{ old('price')?old('price'):$tariff->price }}" 
                    class="w-2/3" placeholder="" required>
                <select name="currency" class="w-1/3">
                    <option value="RUB" selected="">RUB</option>
                </select>
            </div>
            @error('price')
                <div class="error-description">
                    @lang('subscriptions::admin/tariffs.'.$message)
                </div>
            @enderror
        </div>
    </div>
    <div class="flex items-end">
        <div class="form-group w-1/2 @error('period') has-error @enderror">
            <label for="period">@lang('subscriptions::admin/tariffs.period')</label>
            <select name="period" class="block">
                @foreach(config('subscriptions.periods') as $period)
                    <option value="{{ $period }}" @if((old('period')?old('period'):$tariff->period)==$period)selected=""@endif>@lang('subscriptions::periods.'.$period)</option>
                @endforeach
            </select>
            @error('period')
                <div class="error-description">
                    @lang('subscriptions::admin/tariffs.'.$message)
                </div>
            @enderror
        </div>
        <div class="form-group w-1/2 pb-4">
            <input type="checkbox" name="prolongable" value="1" @if(old('prolongable')||$tariff->prolongable)checked="checked"@endif>
            <label for="prolongable">@lang('subscriptions::admin/tariffs.prolongable')</label>
        </div>
    </div>
    <div class="form-group pb-4">
        <input type="checkbox" name="visible" value="1" 
            @if(old('visible') or !old('visible') and $tariff->visible)checked=""@endif>
        <label for="visible">@lang('subscriptions::admin/tariffs.visible')</label>
    </div>

    <h2 class="sub-title">@lang('subscriptions::admin/tariffs.marketing')</h2>

    <div class="flex">
        <div class="form-group w-1/2 @error('crossedPrice') has-error @enderror">
            <label for="crossedPrice">@lang('subscriptions::admin/tariffs.crossedPrice')</label>
            <input type="text" name="crossedPrice" value="{{ old('crossedPrice')?old('crossedPrice'):$tariff->crossedPrice }}" placeholder="">
            @error('crossedPrice')
                <div class="error-description">
                    @lang('subscriptions::admin/tariffs.'.$message)
                </div>
            @enderror
        </div>
    </div>

    @foreach(config('app.locales') as $locale)
        <div class="form-group @error('description.'.$locale) has-error @enderror">
            <label for="description[{{ $locale }}]">@lang('subscriptions::admin/tariffs.description') ({{ $locale }})</label>
            <textarea name="description[{{ $locale }}]" rows="7">{{ old('description.'.$locale)?old('description.'.$locale):$tariff->description->$locale }}</textarea>
            @error('description.'.$locale)
                <div class="error-description">
                    @lang('subscriptions::admin/tariffs.'.$message)
                </div>
            @enderror
        </div>
    @endforeach

    <h2 class="sub-title">@lang('subscriptions::admin/tariffs.features')</h2>

    @foreach(config('subscriptions.features') as $feature)
        <div class="form-group  pb-4">
            <input type="checkbox" name="features[]" value="{{ $feature }}" 
                @if(old('features') and in_array($feature, old('features')) 
                    or !old('features') and is_array($tariff->features) 
                        and in_array($feature, $tariff->features))checked=""@endif>
            <label for="prolongable">@lang('subscriptions::features.'.$feature)</label>
        </div>
    @endforeach

    <div class="form-group text-center">
        <button type="submit" class="button">@lang('subscriptions::admin/tariffs.save')</button>
    </div>
</form>


@endsection