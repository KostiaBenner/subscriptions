@extends('subscriptions::emails.layout')

@section('preheader')
    @lang('subscriptions::emails/cancelled.preheader')
@endsection
@section('content')
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/cancelled.hello', ['name' => $subscription->user->name])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/cancelled.line1', ['app' => __('app.name')])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/cancelled.line2')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/cancelled.line3')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/cancelled.signature', ['name' => __('app.name')])])
@endsection
