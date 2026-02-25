<?php

namespace Wsmallnews\User\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * 侧边栏菜单注册表门面类
 * 
 * 该门面类提供了对侧边栏菜单注册表的静态访问方法，用于管理和获取侧边栏菜单的配置信息。
 *
 * @method static static register(string $module, array|Closure $menuInfo) 注册单个侧边栏菜单项
 * @method static static registers(string $module, array|Closure $menuInfos) 批量注册侧边栏菜单项
 * @method static Collection getModules() 获取所有模块
 * @method static Collection getOriginalMenus(string $module) 获取指定模块的所有原始菜单项
 * @method static static registerSortBy(string $module, array $sortBy = [], string $field = 'label') 注册排序顺序
 * @method static array getSort(string $module) 获取排序顺序
 * @method static string getSortField(string $module) 获取排序字段
 * @method static Collection getMenus(string $module) 获取指定模块的所有处理后的菜单项
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
