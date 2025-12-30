<div class="w-full" >
    <form wire:submit="sendPasswordResetLink">
        {{ $this->form }}
        
        <div class="flex justify-end mt-4">
            <x-filament::button type="submit" class="ml-4">
                发送密码重置链接
            </x-filament::button>
        </div>
    </form>
</div>