<?php

namespace Wsmallnews\User\Livewire\Auth;

use Livewire\Attributes\Title;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class Register extends Base
{
    #[Title('注册')]
    public function render()
    {
        return view($this->getViewPath('auth.register'), [
        ])->layout(Utils::getLayout());
    }
}
