<?php

namespace Wsmallnews\User\Livewire\Components\User;

use Filament\Support\Facades\FilamentView;
use Livewire\Component;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Actions\Logout;

class Profile extends Component
{
    public string $module;

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        // 退出登录
        $logout(UserConfig::getConfig($this->module, 'guard'));

        $this->redirect(UserConfig::getConfig($this->module, 'urls.index'), FilamentView::hasSpaMode());
    }

    public function render()
    {
        return view('sn-user::livewire.components.user.profile', []);
    }
}
