<?php

namespace Wsmallnews\User\Livewire\Auth;

use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class ConfirmPassword extends Base
{
    public function render()
    {
        return view($this->getViewPath('auth.confirm-password'), [])
            ->layout(Utils::getLayout())
            ->title(__('sn-user::user.titles.confirm_password'));
    }
}
