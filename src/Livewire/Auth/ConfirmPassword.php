<?php

namespace Wsmallnews\User\Livewire\Auth;

use Livewire\Attributes\Title;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class ConfirmPassword extends Base
{
    #[Title('确认密码')]
    public function render()
    {
        return view($this->getViewPath('auth.confirm-password'), [
        ])->layout(Utils::getLayout());
    }
}
