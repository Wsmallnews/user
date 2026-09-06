@php
    use Wsmallnews\User\Facades\UserConfig;
@endphp

<div class="w-full" >
    {{ $this->content }}

    <div class="flex flex-col sn-mt gap-4">
        @if (UserConfig::getConfig($module, 'urls.register'))
            <div class="flex text-sm items-center justify-center">
                {{ __('sn-user::user.links.no_account') }}
                <x-filament::link href="{{ UserConfig::getConfig($module, 'urls.register') }}">
                    {{ __('sn-user::user.links.go_register') }}
                </x-filament::link>
            </div>
        @endif
    </div>
</div>