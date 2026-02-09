@php
    use Filament\Support\Enums\IconSize;
    use Wsmallnews\User\Facades\UserConfig;
    $user = auth()->guard(UserConfig::getConfig($module, 'guard'))->user();
@endphp

<x-filament::dropdown
    placement="bottom-end"
    class="fi-user-menu"
    :teleport="true"
>
    <x-slot name="trigger">
        <button
            aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}"
            type="button"
            class="fi-user-menu-trigger"
        >
            <x-filament-panels::avatar.user :user="$user" loading="lazy" />
        </button>
    </x-slot>

    <x-filament::dropdown.header>
        <div class="flex items-center gap-2">
            <x-filament-panels::avatar.user size="sm" :user="$user" loading="lazy" /> {{ filament()->getUserName($user) }}
        </div>
    </x-filament::dropdown.header>


    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item tag="a" href="{{ UserConfig::getConfig($module, 'urls.profile') }}" >
            个人中心
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>

    @if ($switchDarkMode)
        <x-filament::dropdown.list>
            <x-sn-support::theme-switcher />
        </x-filament::dropdown.list>
    @endif

    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item wire:click="logout">
            <div class="flex flex-row items-center gap-1">
                <x-filament::icon
                    icon="heroicon-m-arrow-left-end-on-rectangle"
                    :size="IconSize::Small"
                />
                退出登录
            </div>
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>
</x-filament::dropdown>