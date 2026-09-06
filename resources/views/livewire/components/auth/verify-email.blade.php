@php
    use Wsmallnews\User\Facades\UserConfig;
@endphp

<div class="w-full" >
    @if (session('status'))
        <x-sn-support::alert class="sn-mb" color="success" :title="session('status')" />
    @endif

    {{ $this->content }}

    <div class="flex flex-col sn-mt gap-4">
        @if ($type === 'register')
            <x-filament::link tag="button" wire:click="logout">
                {{ __('sn-user::user.settings.verify.sign_out') }}
            </x-filament::link>
        @elseif (session('status'))
            <x-filament::button tag="a" color="gray" href="{{ session('verify-previous-url') ?? UserConfig::getConfig($module, 'urls.index') }}">
                {{ __('sn-user::user.settings.verify.continue') }}
            </x-filament::button>
        @endif
    </div>
</div>