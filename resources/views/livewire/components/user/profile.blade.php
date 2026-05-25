@php
    use Filament\Support\Enums\IconSize;
    use Filament\Support\Icons\Heroicon;
    use Wsmallnews\User\Enums\Gender;
    use Wsmallnews\User\Facades\UserConfig;
    $user = auth()->guard(UserConfig::getConfig($module, 'guard'))->user();
@endphp

<div class="w-full flex flex-col items-start gap-4" >
    <div class="w-full flex gap-4">
        <div class="w-32 h-32 rounded-full shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800">
            @if($user->getFilamentAvatarUrl())
                <img class="w-full h-full object-cover" src="{{ $user->getFilamentAvatarUrl() }}" alt="{{ $user->getFilamentName() }}" />
            @else
                <div class="sn-image-placeholder">
                    <x-filament::icon :icon="Heroicon::User" class="w-full h-full" aria-hidden="true" />
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-2">
            <span class="flex items-center text-lg font-bold gap-2">
                {{ $user->getFilamentName() }} 

                @if($user->gender !== Gender::Undisclosed->value)
                    <x-filament::icon @class([
                            'text-blue-500' => $user->gender === Gender::Male->value,
                            'text-pink-500' => $user->gender === Gender::Female->value,
                        ]) :icon="$user->genderIcon" :size="IconSize::Medium" />
                @endif
            </span>
            <span class="text-sm text-gray-500">{{ $user?->email }}</span>
            <span class="text-sm text-gray-500">{{ $user?->birthday }}</span>
        </div>
    </div>

    <x-filament::button color="gray" tag="button" wire:click="logout">
        {{ __('sn-user::user.links.sign_out') }}
    </x-filament::button>

</div>
