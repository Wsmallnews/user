<?php

namespace Wsmallnews\User\Livewire\Auth;

use Livewire\Attributes\Title;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class ResetPassword extends Base
{
    #[Title('重置密码')]
    public function render()
    {
        return view($this->getViewPath('auth.reset-password'), [
        ])->layout(Utils::getLayout());
    }
}
