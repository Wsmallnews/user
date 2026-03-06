<?php

namespace Wsmallnews\User\Livewire\Components\User;

use Filament\Support\Facades\FilamentView;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Wsmallnews\User\Actions\Logout;
use Wsmallnews\User\Facades\UserConfig;

class Profile extends Component
{
    #[Locked]
    public string $module;

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
        return view('sn-user::livewire.components.user.profile', []);
    }
}
