<?php

namespace Wsmallnews\User\Livewire\Auth;

use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class Login extends Base
{
    public function render()
    {
        return view($this->getViewPath('auth.login'), [])
            ->layout(Utils::getLayout())
            ->title(__('sn-user::user.titles.login'));
    }
}
