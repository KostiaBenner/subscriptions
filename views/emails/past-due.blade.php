@extends('subscriptions::emails.layout')

@section('preheader')
    @lang('subscriptions::emails/past-due.preheader')
@endsection
@section('content')
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/past-due.hello', ['name' => $subscription->user->name])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/past-due.line1', ['app' => __('app.name')])])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/past-due.line2')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/past-due.line3')])
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/past-due.signature', ['name' => __('app.name')])])
@endsection
