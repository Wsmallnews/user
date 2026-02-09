@php
    use Wsmallnews\User\Facades\UserConfig;
@endphp

<div class="w-full" >
    <form wire:submit="register">
        {{ $this->form }}
        

        <div class="flex flex-col mt-6 gap-4">
            <x-filament::button type="submit">
                注册
            </x-filament::button>

            @if (UserConfig::getConfig($module, 'urls.login'))
                <div class="flex text-sm items-center justify-center">
                    已有账号？
                    <x-filament::link href="{{ UserConfig::getConfig($module, 'urls.login') }}">
                        去登录
                    </x-filament::link>
                </div>
            @endif
        </div>
    </form>
</div>