<div class="w-full" >
    <form wire:submit="login">
        {{ $this->form }}
        
        
        <div class="flex justify-end mt-4">
            {{-- <x-filament::link type="button" href="{{ route(User::routeNames('register')) }}"> --}}
            <x-filament::link type="button">
                还没有账号？立即注册
            </x-filament::link>

            <x-filament::button type="submit" class="ml-4">
                登录
            </x-filament::button>
        </div>
    </form>
</div>

@assets
<script>

</script>
@endassets