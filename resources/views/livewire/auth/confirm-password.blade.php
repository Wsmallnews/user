<div class="w-full" >
    <form wire:submit="confirmPassword">
        {{ $this->form }}
        
        <div class="flex flex-col mt-6 gap-4">
            <x-filament::button type="submit" class="text-white">
                确认密码
            </x-filament::button>
        </div>
    </form>
</div>