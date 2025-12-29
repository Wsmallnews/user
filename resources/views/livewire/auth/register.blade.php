@php
    use Wsmallnews\User\Facades\AuthsConfig;
@endphp

<div class="w-full" >
    <form wire:submit="login">
        {{ $this->form }}
        
        <div class="flex justify-end mt-4">
            {{-- <x-filament::link type="button" href="{{ route(User::routeNames('register')) }}"> --}}
            <x-filament::link href="{{ AuthsConfig::getConfig($module, 'urls.login') }}">
                已有账号，去登录
            </x-filament::link>

            <x-filament::button type="submit" class="ml-4">
                登录
            </x-filament::button>
        </div>
    </form>
</div>