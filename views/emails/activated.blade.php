@extends('subscriptions::emails.layout')

@section('preheader')
    @lang('subscriptions::emails/activated.preheader')
@endsection
@section('content')
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/activated.hello', ['name' => $subscription->user->name])])
    @if($subscription->isPaid())
        @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/activated.paid1', ['tariff' => $subscription->name ])])
        @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/activated.paid2')])
    @endif
    @if($subscription->isTrial())
        @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/activated.trial1', ['tariff' => $subscription->name ])])
        @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/activated.trial2')])
    @endif
    @if($subscription->isEndless() and $subscription->price == 0)
        @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/activated.free1', ['tariff' => $subscription->name ])])
        @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/activated.free2')])
    @endif
    @include('subscriptions::emails.parts.line', ['text' => __('subscriptions::emails/activated.signature', ['name' => __('app.name')])])
@endsection
