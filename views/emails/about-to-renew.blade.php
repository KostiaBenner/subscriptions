@extends('subscriptions::emails.layout')

@section('preheader')
    @lang('subscriptions::emails/about-to-renew.preheader')
@endsection
@section('content')
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/about-to-renew.hello', ['name' => $subscription->user->name])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/about-to-renew.line1', ['app' => __('app.name')])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/about-to-renew.line2')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/about-to-renew.line3')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/about-to-renew.signature', ['name' => __('app.name')])])
@endsection
