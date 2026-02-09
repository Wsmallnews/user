@php
    use Wsmallnews\User\Facades\UserConfig;
@endphp

<div class="w-full" >
    <form wire:submit="sendVerification">
        @if (session('status'))
            <x-sn-support::alert class="mb-4" color="success" :title="session('status')" />
        @endif

        {{ $this->form }}

        <div class="flex flex-col mt-6 gap-4">
            <x-filament::button type="submit">
                {{ $type === 'register' || $type === 'update' || session('status') ? '重新发送验证邮箱' : '发送验证邮箱' }}
            </x-filament::button>

            @if ($type === 'register')
                <x-filament::link tag="button" wire:click="logout">
                    注销登录
                </x-filament::link>
            @elseif (session('status'))
                <x-filament::button tag="a" color="gray" href="{{ session('verify-previous-url') ?? UserConfig::getConfig($module, 'urls.index') }}">
                    我已验证，继续操作
                </x-filament::button>
            @endif
        </div>
    </form>
</div>