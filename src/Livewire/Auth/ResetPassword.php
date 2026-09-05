<?php

namespace Wsmallnews\User\Livewire\Auth;

use Wsmallnews\Support\Facades\Seo;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class ResetPassword extends Base
{
    public function render()
    {
        Seo::title(__('sn-user::user.titles.reset_password'))->robots('noindex');

        return view($this->getViewPath('auth.reset-password'), [])
            ->layout(Utils::getLayout());
    }
}
