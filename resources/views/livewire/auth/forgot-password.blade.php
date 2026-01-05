@php
    use Wsmallnews\User\Facades\AuthsConfig;
@endphp

<div class="w-full" >
    <form wire:submit="sendPasswordResetLink">
        {{ $this->form }}
        
        <div class="flex flex-col mt-6 gap-4">
            <x-filament::button type="submit" class="text-white">
                发送密码重置链接
            </x-filament::button>

            @if (AuthsConfig::getConfig($module, 'urls.login'))
                <div class="flex text-sm items-center justify-center">
                    或者
                    <x-filament::link href="{{ AuthsConfig::getConfig($module, 'urls.login') }}">
                        去登录
                    </x-filament::link>
                </div>
            @endif
        </div>
    </form>
</div>