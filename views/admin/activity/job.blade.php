<div>
    <b>
        @lang('subscriptions::admin/activity.typeJob') 
        {{ $activity->properties['job'] }}
    </b>
</div>
<div>
    <span class="font-medium">@lang('subscriptions::admin/activity.processed')</span>: 
    {{ $activity->properties['processed'] }}
</div>