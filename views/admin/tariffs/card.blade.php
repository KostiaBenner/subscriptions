        <div class="user-card hover:bg-indigo-100">
            <div class="">
                <a href="/tariffs/{{ $tariff->id }}">{{ $tariff->name }}</a>
                <span class="text-sm ml-2">{{ $tariff->slug }}</span>
                <div>
                    <span class="text-sm text-gray-500">
                        {{ $tariff->price }} {{ $tariff->currency }}
                        / @lang('subscriptions::periods.'.$tariff->period)
                        @if($tariff->prolongable)
                            / @lang('admin/tariffs.prolongable')
                        @endif
                    </span>
                </div>
            </div>
            <div class="ml-auto">
                @if($tariff->price == 0 and $tariff->visible)
                <a href="/tariffs/{{ $tariff->id }}/default" title="@lang('admin/tariffs.default')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" class="inline-block fill-current text-gray-{{ ($tariff->default)?'900':'500' }}"><path class="heroicon-ui" d="M6.1 21.98a1 1 0 0 1-1.45-1.06l1.03-6.03-4.38-4.26a1 1 0 0 1 .56-1.71l6.05-.88 2.7-5.48a1 1 0 0 1 1.8 0l2.7 5.48 6.06.88a1 1 0 0 1 .55 1.7l-4.38 4.27 1.04 6.03a1 1 0 0 1-1.46 1.06l-5.4-2.85-5.42 2.85zm4.95-4.87a1 1 0 0 1 .93 0l4.08 2.15-.78-4.55a1 1 0 0 1 .29-.88l3.3-3.22-4.56-.67a1 1 0 0 1-.76-.54l-2.04-4.14L9.47 9.4a1 1 0 0 1-.75.54l-4.57.67 3.3 3.22a1 1 0 0 1 .3.88l-.79 4.55 4.09-2.15z"/></svg></a>
                @endif
            </div>
            <div class="ml-1">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" class="inline-block fill-current text-gray-{{ $tariff->visible ? '900' : '500' }}"><path class="heroicon-ui" d="M17.56 17.66a8 8 0 0 1-11.32 0L1.3 12.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95zm-9.9-1.42a6 6 0 0 0 8.48 0L20.38 12l-4.24-4.24a6 6 0 0 0-8.48 0L3.4 12l4.25 4.24zM11.9 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>
            </div>
            <div class="ml-1">
                <a href="/tariffs/{{ $tariff->id }}/edit" title="@lang('admin/tariffs.modify')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" class="inline-block fill-current text-gray-500"><path class="heroicon-ui" d="M6.3 12.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H7a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM8 16h2.59l9-9L17 4.41l-9 9V16zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6c0-1.1.9-2 2-2h6a1 1 0 0 1 0 2H4v14h14v-6z"/></svg></a>
            </div>
            <div class="ml-1">
                <form name="tariff_{{ $tariff->id }}_delete" action="/tariffs/{{ $tariff->id }}" method="POST">
                    @csrf 
                    @method('DELETE')
                </form>
                <a href="javascript:document.tariff_{{ $tariff->id }}_delete.submit()" onclick="return confirm('@lang('admin/tariffs.confirmDelete')')" title="@lang('admin/tariffs.delete')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" class="inline-block fill-current text-gray-500"><path class="heroicon-ui" d="M8 6V4c0-1.1.9-2 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8H3a1 1 0 1 1 0-2h5zM6 8v12h12V8H6zm8-2V4h-4v2h4zm-4 4a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0v-6a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0v-6a1 1 0 0 1 1-1z"/></svg></a>
            </div>
        </div>
