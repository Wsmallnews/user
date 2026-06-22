@php
    use Wsmallnews\User\UserPlugin;
    use Wsmallnews\User\Support\Utils;
@endphp

<div {{ $attributes->merge(['class' => 'sn-user-container-page w-full flex flex-col h-dvh']) }}>
    <div class="w-full shrink-0 flex h-32 overflow-hidden bg-top-right bg-cover">
        <div class="container mx-auto flex items-center justify-between">
            <div class="flex gap-4">
                @auth
                    <livewire:sn-user::components.user.menu :module="app(UserPlugin::class)->getId()" switch-dark-mode="{{ Utils::hasDarkMode() && !Utils::hasDarkModeForced() }}" />
                @else
                    <x-filament::button tag="a" href="{{ Utils::route('login') }}">
                        {{ __('sn-user::user.auth.login.submit') }}
                    </x-filament::button>
                    <x-filament::button color="gray" tag="a" href="{{ Utils::route('register') }}">
                        {{ __('sn-user::user.auth.register.submit') }}
                    </x-filament::button>
                @endauth
            </div>
        </div>
    </div>

    <div class="w-full flex flex-col grow">
        {{ $slot }}
    </div>
</div>