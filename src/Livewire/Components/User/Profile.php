<?php

namespace Wsmallnews\User\Livewire\Components\User;

use Filament\Support\Facades\FilamentView;
use Wsmallnews\User\Actions\Logout;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;

class Profile extends Base
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        // 退出登录
        $logout($this->getModuleId(), $this->site());

        $this->redirect(UserConfig::getConfig($this->site(), 'urls.index'), FilamentView::hasSpaMode());
    }

    public function render()
    {
        return view('sn-user::livewire.components.user.profile', []);
    }
}
