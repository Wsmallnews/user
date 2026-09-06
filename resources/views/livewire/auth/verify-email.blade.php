<x-dynamic-component :component="$this->getPageContainer()">
    <div class="sn-page">
        <div class="w-full mx-auto md:w-96 sn-padded">
            <livewire:sn-user::components.auth.verify-email :module="app(\Wsmallnews\User\UserPlugin::class)->getId()" :type="$type" />
        </div>
    </div>
</x-dynamic-component>