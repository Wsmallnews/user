<?php

namespace Wsmallnews\User\Livewire\Components\User;

use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Wsmallnews\User\Facades\UserConfig;

class Menu extends Component
{
    public string $module;

    public bool $switchDarkMode = false;

    /**
     * Log the current user out of the application.
     */
    public function logout(): void
    {
        Auth::guard(UserConfig::getConfig($this->module, 'guard'))->logout();

        Session::invalidate();
        Session::regenerateToken();

        $this->redirect(UserConfig::getConfig($this->module, 'urls.index'), FilamentView::hasSpaMode());
    }

    public function render()
    {
        return view('sn-user::livewire.components.user.menu', []);
    }
}
