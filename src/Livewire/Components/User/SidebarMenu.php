<?php

namespace Wsmallnews\User\Livewire\Components\User;

use Livewire\Attributes\Locked;
use Livewire\Component;

class SidebarMenu extends Component
{
    #[Locked]
    public string $module;

    public function render()
    {
        return view('sn-user::livewire.components.user.sidebar-menu', []);
    }
}
