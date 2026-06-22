<x-dynamic-component :component="$this->getPageContainer()">
    <div class="container mx-auto flex flex-col grow gap-4 my-4">
        @if($breadcrumbs)
            <div class="sn-descript-text w-full flex items-center gap-2 text-left">
                {{ __('sn-user::user.layout.current_location') }}
                <x-sn-support::breadcrumbs :breadcrumbs="$breadcrumbs" />
            </div>
        @endif
        
        <div class="w-full flex flex-col md:flex-row items-start gap-4">
            <div class="w-full md:w-72">
                <livewire:sn-user::components.user.sidebar-menu :module="app(\Wsmallnews\User\UserPlugin::class)->getId()" />
            </div>
            
            <div class="sn-container w-full px-4 py-8">
                <livewire:sn-user::components.user.profile :module="app(\Wsmallnews\User\UserPlugin::class)->getId()" />
            </div>
        </div>
    </div>
</x-dynamic-component>