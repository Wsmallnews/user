@php
    use Wsmallnews\User\Facades\UserConfig;
@endphp

<div class="w-full" >
    @if (session('status'))
        <x-sn-support::alert class="mb-4" color="success" :title="session('status')" />
    @endif

    {{ $this->content }}

    <div class="flex flex-col mt-6 gap-4">
        @if (UserConfig::getConfig($module, 'urls.login'))
            <div class="flex text-sm items-center justify-center">
                或者
                <x-filament::link href="{{ UserConfig::getConfig($module, 'urls.login') }}">
                    去登录
                </x-filament::link>
            </div>
        @endif
    </div>
</div>