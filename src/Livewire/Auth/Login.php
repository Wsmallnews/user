<?php

namespace Wsmallnews\User\Livewire\Auth;

use Livewire\Attributes\Title;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class Login extends Base
{
    #[Title('登录')]
    public function render()
    {
        return view($this->getViewPath('auth.login'), [
        ])->layout(Utils::getLayout());
    }
}
