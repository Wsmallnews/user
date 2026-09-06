@php
    use Wsmallnews\User\Facades\UserConfig;
@endphp

<div class="w-full" >
    @if (session('status'))
        <x-sn-support::alert class="sn-mb" color="success" :title="session('status')" />
    @endif

    {{ $this->content }}

    <div class="flex flex-col sn-mt gap-4">
        @if (UserConfig::getConfig($module, 'urls.login'))
            <div class="flex text-sm items-center justify-center">
                {{ __('sn-user::user.auth.forgot_password.or') }}&nbsp;
                <x-filament::link href="{{ UserConfig::getConfig($module, 'urls.login') }}">
                    {{ __('sn-user::user.links.go_login') }}
                </x-filament::link>
            </div>
        @endif
    </div>
</div>