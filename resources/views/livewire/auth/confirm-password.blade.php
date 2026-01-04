<div class="w-full" >
    <form wire:submit="confirmPassword">
        {{ $this->form }}
        
        <div class="flex justify-end mt-4">
            <x-filament::button type="submit" class="ml-4">
                确认密码
            </x-filament::button>
        </div>
    </form>
</div>