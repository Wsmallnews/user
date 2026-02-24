<x-dynamic-component :component="$this->getPageContainer()">
    <div class="container mx-auto flex flex-col grow gap-4 my-4">
        <div class="w-full mx-auto md:w-96 p-4">
            <livewire:sn-user-components-auth-verify-email :module="app(\Wsmallnews\User\UserPlugin::class)->getId()" :type="$type" />
        </div>
    </div>
</x-dynamic-component>