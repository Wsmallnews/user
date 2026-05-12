@php
    use Wsmallnews\User\Facades\UserConfig;
@endphp

<div class="w-full" >
    {{ $this->content }}

    <div class="flex flex-col mt-6 gap-4">
        @if (UserConfig::getConfig($module, 'urls.login'))
            <div class="flex text-sm items-center justify-center">
                {{ __('sn-user::user.links.has_account') }}
                <x-filament::link href="{{ UserConfig::getConfig($module, 'urls.login') }}">
                    {{ __('sn-user::user.links.go_login') }}
                </x-filament::link>
            </div>
        @endif
    </div>
</div>