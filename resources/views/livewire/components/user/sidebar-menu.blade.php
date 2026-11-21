@php
    use Wsmallnews\Support\Features\Site\SiteContext;
    use Wsmallnews\User\Facades\SidebarMenuRegistry;
@endphp

<x-sn-support::sidebar :sidebar="SidebarMenuRegistry::getMenus($this->getModuleId(), app(SiteContext::class)->requireSite())" />