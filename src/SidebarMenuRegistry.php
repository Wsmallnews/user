<?php

namespace Wsmallnews\User;

use Closure;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Wsmallnews\Support\Features\Site\Site;

use function Filament\Support\generate_icon_html;

class SidebarMenuRegistry
{
    /**
     * 站点级菜单：module => siteId => menus
     */
    protected ?Collection $siteMenus;

    public function __construct()
    {
        $this->siteMenus = collect();
    }

    /**
     * 注册 module sidebar 菜单（按站点）
     *
     * @param  string  $module  模块名称
     * @param  array  $menuInfo  菜单信息数组
     */
    public function register(string $module, array | Closure $menuInfo, Site $site): static
    {
        $sites = $this->siteMenus->get($module, collect());
        $sites->put($site->id(), $sites->get($site->id(), collect())->push($menuInfo));
        $this->siteMenus->put($module, $sites);

        return $this;
    }

    /**
     * 注册多个 sidebar 菜单项（按站点）
     *
     * @param  string  $module  模块名称
     * @param  array  $menuInfos  sidebar 菜单信息数组，每个元素为一个 sidebar 菜单信息数组
     */
    public function registers(string $module, array | Closure $menuInfos, Site $site): static
    {
        $menuInfos = $menuInfos instanceof Closure ? $menuInfos() : $menuInfos;

        foreach ($menuInfos as $menuInfo) {
            $this->register($module, $menuInfo, $site);
        }

        return $this;
    }

    /**
     * 获取指定模块在指定站点的处理后 sidebar 菜单
     *
     * @param  string  $module  模块名称
     * @return Collection sidebar 菜单信息集合
     */
    public function getMenus(string $module, Site $site): Collection
    {
        $originalMenus = $this->siteMenus->get($module, collect())->get($site->id(), collect());

        // 处理 menus
        $menus = $originalMenus->map(function ($menuInfo) {
            // 处理 Closure
            return $menuInfo instanceof Closure ? $menuInfo() : $menuInfo;
        });

        // 过滤隐藏的菜单
        $menus = $menus->filter(function ($menuInfo) {
            // 隐藏状态
            $menuInfo['hidden'] = $menuInfo['hidden'] ?? false;              // 是否隐藏

            return ! ($menuInfo['hidden'] instanceof Closure ? $menuInfo['hidden']() : $menuInfo['hidden']);              // 是否隐藏
        });

        // 处理 menus
        $menus = $menus->map(function ($menuInfo) {
            return $this->handleMenu($menuInfo);
        });

        return $menus;
    }

    /**
     * 处理 sidebar 菜单
     *
     * @param  array | Closure  $menuInfo  sidebar 菜单信息数组
     * @return array 处理后的 sidebar 菜单信息数组
     */
    protected function handleMenu(array | Closure $menuInfo): array
    {
        $fullUrl = request()->fullUrl();

        // 处理 url
        $menuInfo['url'] = $menuInfo['url'] instanceof Closure ? $menuInfo['url']() : $menuInfo['url'];

        // 当前菜单活动状态
        $menuInfo['is_active'] = (bool) ($menuInfo['url'] == $fullUrl);       // 活动状态

        // 处理 label
        $menuInfo['label'] = $this->handleLabel($menuInfo);

        return $menuInfo;
    }

    /**
     * 处理 sidebar 菜单 label
     *
     * @param  array  $menuInfo  sidebar 菜单信息数组
     * @return HtmlString 处理后的 sidebar 菜单 label
     */
    protected function handleLabel(array $menuInfo): HtmlString
    {
        // 处理 label
        $recordLabel = '<span class="flex items-center gap-2">';
        $icon_type = isset($menuInfo['icon']) && $menuInfo['icon'] ? 'icon' : (isset($menuInfo['icon_src']) && $menuInfo['icon_src'] ? 'image' : 'none');
        if ($icon_type == 'icon') {
            if ($menuInfo['is_active']) {
                $icon = $menuInfo['active_icon'] ?? ($menuInfo['icon'] ?? '');        // 优先取 活动图标
            } else {
                $icon = $menuInfo['icon'] ?? ($menuInfo['active_icon'] ?? '');        // 优先取非活动图标
            }
            $icon && $recordLabel .= generate_icon_html($icon, size: IconSize::Medium)->toHtml();
        } elseif ($icon_type == 'image') {
            if ($menuInfo['is_active']) {
                $image = $menuInfo['active_icon_src'] ?? ($menuInfo['icon_src'] ?? '');    // 优先取 活动图标
            } else {
                $image = $menuInfo['icon_src'] ?? ($menuInfo['active_icon_src'] ?? '');   // 优先取非活动图标
            }
            $image && $recordLabel .= '<img src="' . files_url($image) . '" class="size-6" />';
        }

        $recordLabel .= $menuInfo['label'] . '</span>';

        return new HtmlString($recordLabel);
    }
}
