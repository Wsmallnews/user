<div class="flex flex-col w-full mx-auto space-y-6 text-sm" wire:cloak>
    @if ($twoFactorEnabled)
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <x-filament::badge size="xl" color="success">{{ __('sn-user::user.settings.two_factor.enabled_badge') }}</x-filament::badge>
            </div>

            <div class="text-gray-500">
                {{ __('sn-user::user.settings.two_factor.two_factor_info_enabled') }}
            </div>

            <livewire:sn-user-components-settings-two-factor-recovery-codes :module="$module" :$requiresConfirmation />

            <div class="flex justify-start">
                {{ $this->disableAction }}
            </div>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <x-filament::badge size="xl" color="danger">{{ __('sn-user::user.settings.two_factor.disabled_badge') }}</x-filament::badge>
            </div>

            <div class="text-gray-500">
                {{ __('sn-user::user.settings.two_factor.two_factor_info_disabled') }}
            </div>

            {{ $this->enableAction }}
        </div>
    @endif
    <x-filament-actions::modals />
</div>