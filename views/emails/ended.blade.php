@extends('subscriptions::emails.layout')

@section('preheader')
    @lang('subscriptions::emails/ended.preheader')
@endsection
@section('content')
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/ended.hello', ['name' => $subscription->user->name])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/ended.line1', ['tariff' => $subscription->name ])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/ended.line2')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/ended.line3')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/ended.signature', ['name' => __('app.name')])])
@endsection
