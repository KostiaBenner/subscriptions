@extends('subscriptions::emails.layout')

@section('preheader')
    @lang('subscriptions::emails/renewed.preheader')
@endsection
@section('content')
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/renewed.hello', ['name' => $subscription->user->name])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/renewed.line1', ['app' => __('app.name')])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/renewed.line2')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/renewed.line3')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/renewed.signature', ['name' => __('app.name')])])
@endsection
