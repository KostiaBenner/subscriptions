        <div class="user-card hover:bg-indigo-100">
            <div class="">
                <div  class="text-sm text-gray-500 mb-2">
                    {{ $activity->created_at->format('d.m.Y H:i:s') }}
                </div>
                @if($activity->subject_type)
                    @include('subscriptions::admin.activity.model')
                @else
                    @if($activity->description == 'executed')
                        @include('subscriptions::admin.activity.job')
                    @elseif($activity->description == 'requested')
                        @include('subscriptions::admin.activity.payment')
                    @endif
                @endif
            </div>
        </div>
