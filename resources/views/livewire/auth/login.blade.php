@php
    use Wsmallnews\User\Facades\AuthsConfig;
@endphp

<div class="w-full" >
    <form wire:submit="login">
        {{ $this->form }}
        
        <div class="flex flex-col mt-6 gap-4">
            <x-filament::button type="submit" class="text-white">
                登录
            </x-filament::button>

            @if (AuthsConfig::getConfig($module, 'urls.register'))
                <div class="flex text-sm items-center justify-center">
                    还没有账号？
                    <x-filament::link href="{{ AuthsConfig::getConfig($module, 'urls.register') }}">
                        去注册
                    </x-filament::link>
                </div>
            @endif
        </div>
    </form>
</div>