<div class="flex flex-col w-full mx-auto space-y-6 text-sm" wire:cloak>
    @if ($twoFactorEnabled)
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <x-filament::badge size="xl" color="success">{{ __('Enabled') }}</x-filament::badge>
            </div>

            <div class="text-gray-500">
                {{ __('With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
            </div>

            <livewire:sn-user-components-settings-two-factor-recovery-codes :module="$module" :$requiresConfirmation />

            <div class="flex justify-start">
                <x-filament::button type="button" 
                    color="danger"
                    class="text-white"
                    icon="heroicon-m-shield-exclamation"
                    wire:click="disable"
                >
                    {{ __('Disable 2FA') }}
                </x-filament::button>
            </div>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <x-filament::badge size="xl" color="danger">{{ __('Disabled') }}</x-filament::badge>
            </div>

            <div class="text-gray-500">
                {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
            </div>

            <x-filament::button type="button" 
                class="text-white"
                icon="heroicon-m-shield-check"
                wire:click="enable"
            >
                {{ __('Enable 2FA') }}
            </x-filament::button>
        </div>
    @endif
</div>