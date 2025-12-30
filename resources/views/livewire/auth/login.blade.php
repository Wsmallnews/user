@php
    use Wsmallnews\User\Facades\AuthsConfig;
@endphp

<div class="w-full" >
    <form wire:submit="login">
        {{ $this->form }}
        
        <div class="flex justify-end mt-4">
            @if (AuthsConfig::getConfig($module, 'urls.register'))
                <x-filament::link href="{{ AuthsConfig::getConfig($module, 'urls.register') }}">
                    还没有账号？去注册
                </x-filament::link>
            @endif

            <x-filament::button type="submit" class="ml-4">
                登录
            </x-filament::button>
        </div>
    </form>
</div>