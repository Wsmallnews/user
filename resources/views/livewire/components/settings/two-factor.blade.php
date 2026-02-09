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
                {{ $this->disableAction }}
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

            {{ $this->enableAction }}
        </div>
    @endif
    <x-filament-actions::modals />
</div>