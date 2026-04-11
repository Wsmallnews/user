@php
    use Filament\Support\Enums\IconSize;
    use Wsmallnews\User\Enums\Gender;
    use Wsmallnews\User\Facades\UserConfig;
    $user = auth()->guard(UserConfig::getConfig($module, 'guard'))->user();
@endphp

<div class="w-full flex flex-col items-start gap-4" >
    <div class="w-full flex gap-4">
        <img src="{{ files_url($user->getFilamentAvatarUrl()) }}" alt="{{ $user->getFilamentName() }}" class="w-32 h-32 rounded-full" />

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
        注销登录
    </x-filament::button>

</div>
