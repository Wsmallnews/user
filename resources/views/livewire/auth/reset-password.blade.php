<div class="w-full" >
    <form wire:submit="resetPassword">
        {{ $this->form }}
        
        <div class="flex flex-col mt-6 gap-4">
            <x-filament::button type="submit" class="text-white">
                重置密码
            </x-filament::button>
        </div>
    </form>
</div>