<?php

namespace Wsmallnews\User\Livewire\Auth;

use Livewire\Attributes\Title;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class ForgotPassword extends Base
{
    #[Title('忘记密码')]
    public function render()
    {
        return view($this->getViewPath('auth.forgot-password'), [
        ])->layout(Utils::getLayout());
    }
}
