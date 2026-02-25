<?php

namespace Wsmallnews\User\Livewire\Components\User;

use Livewire\Component;

class SidebarMenu extends Component
{
    public string $module;

    public function render()
    {
        return view('sn-user::livewire.components.user.sidebar-menu', []);
    }
}
