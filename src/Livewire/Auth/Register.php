<?php

namespace Wsmallnews\User\Livewire\Auth;

use Wsmallnews\Support\Facades\Seo;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class Register extends Base
{
    public function render()
    {
        Seo::title(__('sn-user::user.titles.register'))->robots('noindex');

        return view($this->getViewPath('auth.register'), [])
            ->layout(Utils::getLayout());
    }
}
