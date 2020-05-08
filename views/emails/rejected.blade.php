@extends('subscriptions::emails.layout')

@section('preheader')
    @lang('subscriptions::emails/rejected.preheader')
@endsection
@section('content')
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/rejected.hello', ['name' => $subscription->user->name])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/rejected.line1', ['app' => __('app.name')])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/rejected.line2')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/rejected.line3')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/rejected.signature', ['name' => __('app.name')])])
@endsection
