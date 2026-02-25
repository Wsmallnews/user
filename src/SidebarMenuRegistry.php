<?php

namespace Wsmallnews\User;

use Closure;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

use function Filament\Support\generate_icon_html;

class SidebarMenuRegistry
{
    /**
     * 存储所有 module sidebar 信息的集合
     */
    protected ?Collection $menus;

    /**
     * 存储所有 module sidebar 排序信息的集合
     */
    protected ?Collection $sortBy;

    public function __construct()
    {
        $this->menus = collect();
        $this->sortBy = collect();
    }

    /**
     * 注册 module sidebar 菜单
     *
     * @param  string  $module  模块名称
     * @param  array  $menuInfo  菜单信息数组
     */
    public function register(string $module, array | Closure $menuInfo): static
    {
        $menus = $this->getOriginalMenus($module);

        $this->menus->put($module, $menus->push($menuInfo));

        return $this;
    }

    /**
     * 注册多个范围类型
     *
     * @param  string  $module  模块名称
     * @param  array  $menuInfos  sidebar 菜单信息数组，每个元素为一个 sidebar 菜单信息数组
     */
    public function registers(string $module, array | Closure $menuInfos): static
    {
        $menuInfos = $menuInfos instanceof Closure ? $menuInfos() : $menuInfos;

        foreach ($menuInfos as $menuInfo) {
            $this->register($module, $menuInfo);
        }

        return $this;
    }

    /**
     * 获取所有模块
     *
     * @return Collection 所有模块
     */
    public function getModules(): Collection
    {
        return $this->menus;
    }


    /**
     * 获取指定模块的所有 sidebar 菜单 （原始值，里面会有 Closure）
     *
     * @param  string  $module  模块名称
     * @return Collection sidebar 菜单信息集合
     */
    public function getOriginalMenus(string $module): Collection
    {
        return $this->menus->get($module, collect());
    }


    /**
     * 注册排序顺序
     *
     * @param string $module
     * @param array $sortBy
     * @param string $field
     * @return void
     */
    public function registerSortBy(string $module, array $sortBy = [], string $field = 'label'): static
    {
        $this->sortBy->put($module, [
            'sort' => $sortBy,
            'field' => $field,
        ]);

        return $this;
    }


    /**
     * 获取排序顺序 sort
     *
     * @param string $module
     * @return array
     */
    public function getSort(string $module): array
    {
        return $this->sortBy->get($module, [])['sort'] ?? [];
    }

    /**
     * 获取排序字段
     *
     * @param string $module
     * @return string
     */
    public function getSortField(string $module): string
    {
        return $this->sortBy->get($module, [])['field'] ?? 'label';
    }

    /**
     * 获取指定模块的所有 sidebar 菜单
     *
     * @param  string  $module  模块名称
     * @return Collection sidebar 菜单信息集合
     */
    public function getMenus(string $module): Collection
    {
        $originalMenus = $this->getOriginalMenus($module);

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

        // 按照给定的排序字段排序
        $menus = $this->sortMenus($module, $menus);

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
        $menuInfo['is_active'] = (bool)($menuInfo['url'] == $fullUrl);       // 活动状态

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


    /**
     * 根据传入排序顺序字段，排序 sidebar 菜单
     *
     * @param string $module
     * @param Collection $menus
     * @return Collection
     */
    protected function sortMenus(string $module, Collection $menus): Collection
    {
        $sorts = $this->getSort($module);
        $sortField = $this->getSortField($module);
        return $menus->sortBy(function ($menu) use ($sorts, $sortField) {
            $position = array_search($menu[$sortField], $sorts);

            // 在顺序数组中的按位置排序，不在的放在最后
            return $position === false ? 999 : $position;
        })->values();
    }
}
