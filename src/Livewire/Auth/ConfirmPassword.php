<?php

namespace Wsmallnews\User\Livewire\Auth;

use Wsmallnews\Support\Facades\Seo;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class ConfirmPassword extends Base
{
    public function render()
    {
        Seo::title(__('sn-user::user.titles.confirm_password'))->robots('noindex');

        return view($this->getViewPath('auth.confirm-password'), [])
            ->layout(Utils::getLayout());
    }
}
