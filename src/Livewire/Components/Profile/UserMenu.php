<?php

namespace Wsmallnews\User\Livewire\Components\Profile;

use Filament\Support\Facades\FilamentView;
use Livewire\Component;
use Wsmallnews\User\Facades\AuthsConfig;
use Wsmallnews\User\Livewire\Actions\Logout;

class UserMenu extends Component
{
    public string $module;

    public bool $darkMode = false;

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        // 退出登录
        $logout(AuthsConfig::getConfig($this->module, 'guard'));

        $this->redirect(AuthsConfig::getConfig($this->module, 'urls.index'), FilamentView::hasSpaMode());
    }

    public function render()
    {
        return view('sn-user::livewire.profile.user-menu', []);
    }
}
