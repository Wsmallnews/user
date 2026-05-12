<?php

namespace Wsmallnews\User\Livewire\Components\User;

use Livewire\Attributes\Locked;
use Wsmallnews\User\Livewire\Components\Base;

class SidebarMenu extends Base
{
    #[Locked]
    public string $module;

    public function render()
    {
        return view('sn-user::livewire.components.user.sidebar-menu', []);
    }
}
