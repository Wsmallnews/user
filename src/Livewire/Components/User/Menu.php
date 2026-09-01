<?php

namespace Wsmallnews\User\Livewire\Components\User;

use Filament\Support\Facades\FilamentView;
use Livewire\Attributes\Locked;
use Wsmallnews\User\Actions\Logout;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;

class Menu extends Base
{
    #[Locked]
    public string $module;

    public bool $switchDarkMode = false;

    /**
     * 下拉弹卡位置（Filament dropdown placement）；
     * 页头靠右用 bottom-end，窄屏移动菜单靠左用 bottom-start 避免超出视口
     */
    public string $placement = 'bottom-end';

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        // 退出登录
        $logout($this->module);

        $this->redirect(UserConfig::getConfig($this->module, 'urls.index'), FilamentView::hasSpaMode());
    }

    public function render()
    {
        return view('sn-user::livewire.components.user.menu', []);
    }
}
