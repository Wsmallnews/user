@php
    use Wsmallnews\User\Facades\UserConfig;
@endphp

<div class="w-full" >
    {{ $this->content }}

    <div class="flex flex-col mt-6 gap-4">
        @if (UserConfig::getConfig($module, 'urls.register'))
            <div class="flex text-sm items-center justify-center">
                还没有账号？
                <x-filament::link href="{{ UserConfig::getConfig($module, 'urls.register') }}">
                    去注册
                </x-filament::link>
            </div>
        @endif
    </div>
</div>