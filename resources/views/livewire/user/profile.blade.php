@php
    use Filament\Support\Enums\IconSize;
    use Wsmallnews\User\Facades\UserConfig;
    $user = auth()->guard(UserConfig::getConfig($module, 'guard'))->user();
@endphp

<div class="w-full" >
    <x-filament::link tag="button" wire:click="logout">
        注销登录
    </x-filament::link>

</div>
