<?php

namespace Wsmallnews\User\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * 侧边栏菜单注册表门面类
 *
 * 该门面类提供了对侧边栏菜单注册表的静态访问方法，用于管理和获取侧边栏菜单的配置信息。
 *
 * @method static static register(string $module, array|Closure $menuInfo, Site $site) 注册单个侧边栏菜单项
 * @method static static registers(string $module, array|Closure $menuInfos, Site $site) 批量注册侧边栏菜单项
 * @method static Collection getMenus(string $module, Site $site) 获取指定模块在指定站点的处理后菜单项
 *
 * @see \Wsmallnews\User\SidebarMenuRegistry 实际实现类
 */
class SidebarMenuRegistry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wsmallnews\User\SidebarMenuRegistry::class;
    }
}
