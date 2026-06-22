<x-dynamic-component :component="$this->getPageContainer()">
    <div class="container mx-auto flex flex-col grow gap-4 my-4">
        <div class="w-full mx-auto md:w-96 p-4">
            <livewire:sn-user::components.auth.forgot-password :module="app(\Wsmallnews\User\UserPlugin::class)->getId()" />
        </div>
    </div>
</x-dynamic-component>