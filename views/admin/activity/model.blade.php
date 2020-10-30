<div>
    <b>
        @lang('subscriptions::admin/activity.type'.Str::afterLast($activity->subject_type, '\\')):
        {{ $activity->subject->name ?? $activity->properties['attributes']['name'] }}
    </b>
    @if(Str::afterLast($activity->subject_type, '\\') == 'Subscription')
        -> @lang('subscriptions::admin/activity.typeUser'):
        {{ $activity->subject->user->name ?? __('subscriptions::admin/activity.gone') }}
    @endif
</div>
<div>
    <i>@lang('subscriptions::admin/activity.'.$activity->description)</i>
    @if ($activity->description == 'updated')
        @foreach($activity->properties['old'] as $name => $oldValue)
            @if($name != 'updated_at')
                <div>
                    <span class="font-medium">{{ $name }}</span>: {{ $oldValue }} -> 
                    @if(Str::endsWith($name, 'date'))
                        {{ Carbon\Carbon::parse($activity->properties['attributes'][$name])->format('d.m.Y H:i:s') }}
                    @else
                        {{ $activity->properties['attributes'][$name] }}
                    @endif
                    
                </div>
            @endif
        @endforeach
    @else
        @foreach($activity->properties['attributes'] as $name => $value)
            @if($value && $name != 'password' && ! Str::endsWith($name, '_at'))
                <div>
                    <span class="font-medium">{{ $name }}</span>: 
                    @if(Str::endsWith($name, 'date'))
                        {{ Carbon\Carbon::parse($value)->format('d.m.Y H:i:s') }}
                    @else
                        {{ $value }}
                    @endif
                </div>
            @endif
        @endforeach
    @endif
</div>
