@extends('subscriptions::emails.layout')

@section('preheader')
    @lang('subscriptions::emails/about-to-end.preheader')
@endsection
@section('content')
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/about-to-end.hello', ['name' => $subscription->user->name])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/about-to-end.line1', ['tariff' => $subscription->name ])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/about-to-end.line2')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/about-to-end.line3')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/about-to-end.signature', ['name' => __('app.name')])])
@endsection
