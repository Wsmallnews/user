<div
    class="py-6 space-y-6 border shadow-sm rounded-xl border-zinc-200 dark:border-white/10"
    wire:cloak
    x-data="{ showRecoveryCodes: false }"
>
    <div class="px-6 space-y-2">
        <div class="flex items-center gap-2">
            <x-filament::icon icon="heroicon-o-lock-closed" />
            <div class="text-lg">{{ __('2FA Recovery Codes') }}</div>
        </div>

        <div class="text-sm text-gray-500">{{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}</div>
    </div>

    <div class="px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <x-filament::button
                x-show="!showRecoveryCodes"
                icon="heroicon-o-eye"
                color="info"
                @click="showRecoveryCodes = true;"
                aria-expanded="false"
                aria-controls="recovery-codes-section"
            >
                {{ __('View Recovery Codes') }}
            </x-filament::button>

            <x-filament::button
                x-show="showRecoveryCodes"
                icon="heroicon-o-eye-slash"
                color="info"
                @click="showRecoveryCodes = false"
                aria-expanded="true"
                aria-controls="recovery-codes-section"
            >
                {{ __('Hide Recovery Codes') }}
            </x-filament::button>

            @if (filled($recoveryCodes))
                <template x-if="showRecoveryCodes">
                    {{ $this->regenerateAction }}
                </template>
            @endif
        </div>

        <div
            x-show="showRecoveryCodes"
            x-transition
            id="recovery-codes-section"
            class="relative overflow-hidden"
            x-bind:aria-hidden="!showRecoveryCodes"
        >
            <div class="mt-3 space-y-3">
                @if (filled($recoveryCodes))
                    <div
                        class="grid gap-1 p-4 font-mono text-sm rounded-lg bg-zinc-100 dark:bg-white/5"
                        role="list"
                        aria-label="Recovery codes"
                    >
                        @foreach($recoveryCodes as $code)
                            <div
                                role="listitem"
                                class="select-text"
                                wire:loading.class="opacity-50 animate-pulse"
                            >
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                    <div class="text-sm text-gray-500">{{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate Codes above.') }}}</div>
                @endif
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</div>