<x-dynamic-component :component="$this->getPageContainer()">
    <div class="sn-page">
        <div class="w-full mx-auto md:w-96 sn-padded">
            <livewire:sn-user::components.auth.register :module="app(\Wsmallnews\User\UserPlugin::class)->getId()" />
        </div>
    </div>
</x-dynamic-component>