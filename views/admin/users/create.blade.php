@extends('admin.layout')

@section('content')
<h1 class="page-header">
    <a href="/users" class="text-white">@lang('subscriptions::admin/users.listTitle')</a> 
</h1>
<h2 class="sub-header">@lang('subscriptions::admin/users.creation')</h2>

<form autocomplete="off" method="post" action="/users">
    @csrf
    <div class="form-group @error('name') has-error @enderror">
        <label for="name">@lang('subscriptions::admin/users.name')</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="" required>
        @error('name')
            <div class="error-description">
                @lang('subscriptions::admin/users.'.$message)
            </div>
        @enderror
    </div>
    <div class="form-group  @error('email') has-error @enderror">
        <label for="email">@lang('subscriptions::admin/users.email')</label>
        <input type="text" name="email" value="{{ old('email') }}" placeholder="" required>
        @error('email')
            <div class="error-description">
                @lang('subscriptions::admin/users.'.$message)
            </div>
        @enderror
    </div>
    <div class="form-group ">
        <input type="checkbox" name="dontVerify" value="1" @if(old('dontVerify'))checked=""@endif>
        <label for="dontVerify">@lang('subscriptions::admin/users.dontVerify')</label>
    </div>
    <div class="form-group  @error('password') has-error @enderror">
        <label for="password">@lang('subscriptions::admin/users.password')</label>
        <input type="password" name="password" value="{{ old('password') }}" required>
        @error('password')
            <div class="error-description">
                @lang('subscriptions::admin/users.'.$message)
            </div>
        @enderror
    </div>
    <div class="form-group  @error('password') has-error @enderror">
        <label for="password_confirmation">@lang('subscriptions::admin/users.password_confirmation')</label>
        <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}" required>
    </div>
    <div class="form-group @error('role') has-error @enderror">
        <label for="role">@lang('subscriptions::admin/users.role')</label>
        <select name="role" class="block">
            @for($role=1 ; $role<Auth::user()->role ; $role++)
                <option value="{{ $role }}" @if(old('role')==$role)selected=""@endif>@lang('app.role'.$role)</option>
            @endfor
        </select>
        @error('role')
            <div class="error-description">
                @lang('subscriptions::admin/users.'.$message)
            </div>
        @enderror
    </div>
    <div class="form-group text-center">
        <button type="submit" class="button">@lang('subscriptions::admin/users.save')</button>
    </div>
</form>


@endsection