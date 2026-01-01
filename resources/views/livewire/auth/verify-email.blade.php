<div class="w-full" >
    <form wire:submit="sendVerification">
        {{ $this->form }}

        <div class="flex justify-between mt-4">
            <x-filament::button type="submit">
                重新发送验证邮箱
            </x-filament::button>

            <x-filament::link tag="button" wire:click="logout" class="text-underline">
                注销登录
            </x-filament::link>
        </div>
    </form>
</div>