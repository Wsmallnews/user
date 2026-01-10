@php
    use Wsmallnews\User\Facades\UserConfig;
@endphp

<div class="w-full" >
    <form wire:submit="sendPasswordResetLink">
        @if (session('status'))
            <x-sn-support::alert class="mb-4" color="success" :title="session('status')" />
        @endif

        {{ $this->form }}
        
        <div class="flex flex-col mt-6 gap-4">
            <x-filament::button type="submit" class="text-white">
                发送密码重置链接
            </x-filament::button>

            @if (UserConfig::getConfig($module, 'urls.login'))
                <div class="flex text-sm items-center justify-center">
                    或者
                    <x-filament::link href="{{ UserConfig::getConfig($module, 'urls.login') }}">
                        去登录
                    </x-filament::link>
                </div>
            @endif
        </div>
    </form>
</div>