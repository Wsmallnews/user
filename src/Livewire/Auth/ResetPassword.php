<?php

namespace Wsmallnews\User\Livewire\Auth;

use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class ResetPassword extends Base
{
    public function render()
    {
        return view($this->getViewPath('auth.reset-password'), [])
            ->layout(Utils::getLayout())
            ->title(__('sn-user::user.titles.reset_password'));
    }
}
