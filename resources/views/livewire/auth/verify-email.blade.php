<div class="w-full" >
    <form wire:submit="sendVerification">
        {{ $this->form }}

        <div class="flex flex-col mt-6 gap-4">
            <x-filament::button type="submit" class="text-white">
                重新发送验证邮箱
            </x-filament::button>

            <x-filament::link tag="button" wire:click="logout">
                注销登录
            </x-filament::link>
        </div>
    </form>
</div>