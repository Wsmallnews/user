@php
    use Wsmallnews\User\Facades\AuthsConfig;
@endphp

<div class="w-full" >
    <form wire:submit="register">
        {{ $this->form }}
        
        <div class="flex justify-end mt-4">
            @if (AuthsConfig::getConfig($module, 'urls.login'))
                <x-filament::link href="{{ AuthsConfig::getConfig($module, 'urls.login') }}">
                    已有账号？去登录
                </x-filament::link>
            @endif

            <x-filament::button type="submit" class="ml-4">
                注册
            </x-filament::button>
        </div>
    </form>
</div>