        <div class="user-card hover:bg-indigo-100">
            @if($user->hasVerifiedEmail())
                <div class="status-square bg-indigo-400">
                    <svg class="fill-current text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><path class="heroicon-ui" d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6c0-1.1.9-2 2-2zm16 3.38V6H4v1.38l8 4 8-4zm0 2.24l-7.55 3.77a1 1 0 0 1-.9 0L4 9.62V18h16V9.62z"/></svg>
                </div>
            @else
                <div class="status-square bg-indigo-300">
                    <svg class="fill-current text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><path class="heroicon-ui" d="M18.59 13H3a1 1 0 0 1 0-2h15.59l-5.3-5.3a1 1 0 1 1 1.42-1.4l7 7a1 1 0 0 1 0 1.4l-7 7a1 1 0 0 1-1.42-1.4l5.3-5.3z"/></svg>
                </div>
            @endif
            <div class="">
                <a href="/users/{{ $user->id }}">{{ $user->name }}</a>
                <span class="text-sm ml-2">{{ $user->email }}</span>
                <div  class="text-sm text-gray-500 mb-2">
                    @lang('app.role'.$user->role) /
                    @lang('admin/users.registered') {{ $user->created_at->format('d.m.Y') }}
                </div>
                @if($user->subscription())
                    <div class="text-sm text-gray-500">
                        @lang('admin/users.tariff') {{ $user->subscription()->name }}
                        @if($user->subscription()->isTrial())
                            @lang('admin/users.till') 
                            {{ $user->subscription()->next_transaction_date->format('d.m.Y') }}
                        @endif
                    </div>
                @endif
            </div>
            <div class="ml-auto">
                <a href="/support/dialog/{{ $user->id }}">
                    <svg class="inline-block fill-current text-gray-500" width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M6 14H4a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h12a2 2 0 0 1 2 2v2h2a2 2 0 0 1 2 2v13a1 1 0 0 1-1.7.7L16.58 18H8a2 2 0 0 1-2-2v-2zm0-2V8c0-1.1.9-2 2-2h8V4H4v8h2zm14-4H8v8h9a1 1 0 0 1 .7.3l2.3 2.29V8z"/></svg>
            </div>
            <div class="ml-1">
                <a href="/users/{{ $user->id }}/edit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" class="inline-block fill-current text-gray-500"><path class="heroicon-ui" d="M6.3 12.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H7a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM8 16h2.59l9-9L17 4.41l-9 9V16zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6c0-1.1.9-2 2-2h6a1 1 0 0 1 0 2H4v14h14v-6z"/></svg></a>
            </div>
            <div class="ml-1">
                <a href="javascript:document.user_{{ $user->id }}_delete.submit()" onclick="return confirm('@lang('admin/users.confirmDelete')')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" class="inline-block fill-current text-gray-500"><path class="heroicon-ui" d="M8 6V4c0-1.1.9-2 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8H3a1 1 0 1 1 0-2h5zM6 8v12h12V8H6zm8-2V4h-4v2h4zm-4 4a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0v-6a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0v-6a1 1 0 0 1 1-1z"/></svg></a>
                <form name="user_{{ $user->id }}_delete" action="/users/{{ $user->id }}" method="POST">
                    @csrf 
                    @method('DELETE')
                </form>
            </div>
        </div>
