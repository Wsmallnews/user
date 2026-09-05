<?php

namespace Wsmallnews\User\Livewire\Auth;

use Wsmallnews\Support\Facades\Seo;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class ForgotPassword extends Base
{
    public function render()
    {
        Seo::title(__('sn-user::user.titles.forgot_password'))->robots('noindex');

        return view($this->getViewPath('auth.forgot-password'), [])
            ->layout(Utils::getLayout());
    }
}
