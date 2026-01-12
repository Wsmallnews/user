@php
    use Wsmallnews\User\Facades\UserConfig;
@endphp

<div class="w-full" >
    <form wire:submit="updateProfileInformation">
        {{ $this->form }}
        
        <div class="flex flex-col mt-6 gap-4">
            <x-filament::button type="submit">
                保存
            </x-filament::button>
        </div>
    </form>
</div>