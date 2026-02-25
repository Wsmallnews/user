@php
    use Wsmallnews\User\Facades\SidebarMenuRegistry;
@endphp

<x-sn-support::sidebar :sidebar="SidebarMenuRegistry::getMenus($module)" />