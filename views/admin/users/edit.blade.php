@extends('admin.layout')

@section('content')
<h1 class="page-header">
    <a href="/users" class="text-white">@lang('admin/users.listTitle')</a> 
</h1>
<h2 class="sub-header"><a href="/users/{{ $user->id }}">{{ $user->name }}</a></h2>

<form autocomplete="off" method="post" action="/users/{{ $user->id }}">
    @csrf
    @method('PATCH')
    <div class="form-group @error('name') has-error @enderror">
        <label for="name">@lang('admin/users.name')</label>
        <input type="text" name="name" value="{{ old('name')?old('name'):$user->name }}" placeholder="" required>
        @error('name')
            <div class="error-description">
                @lang('admin/users.'.$message)
            </div>
        @enderror
    </div>
    <div class="form-group  @error('email') has-error @enderror">
        <label for="email">@lang('admin/users.email')</label>
        <input type="text" name="email" value="{{ old('email')?old('email'):$user->email }}" placeholder="" required>
        @error('email')
            <div class="error-description">
                @lang('admin/users.'.$message)
            </div>
        @enderror
    </div>
    <div class="form-group  @error('password') has-error @enderror">
        <label for="password">@lang('admin/users.password')</label>
        <input type="password" name="password" value="{{ old('password') }}">
        @error('password')
            <div class="error-description">
                @lang('admin/users.'.$message)
            </div>
        @enderror
    </div>
    <div class="form-group  @error('password') has-error @enderror">
        <label for="password_confirmation">@lang('admin/users.password_confirmation')</label>
        <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}">
    </div>
    <div class="form-group @error('role') has-error @enderror">
        <label for="role">@lang('admin/users.role')</label>
        <select name="role" class="block">
            @for($role=1 ; $role<Auth::user()->role ; $role++)
                <option value="{{ $role }}" @if((old('role')?old('role'):$user->role)==$role)selected=""@endif>@lang('app.role'.$role)</option>
            @endfor
        </select>
        @error('role')
            <div class="error-description">
                @lang('admin/users.'.$message)
            </div>
        @enderror
    </div>
    <div class="form-group text-center">
        <button type="submit" class="button">@lang('admin/users.save')</button>
    </div>
</form>


@endsection