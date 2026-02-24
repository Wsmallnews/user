<?php

namespace Wsmallnews\User\Livewire\Auth;

use Livewire\Attributes\Title;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class VerifyEmail extends Base
{
    public string $type = 'check';

    public function mount()
    {
        $this->type = request()->query('type', 'check');
    }

    #[Title('验证邮箱')]
    public function render()
    {
        return view($this->getViewPath('auth.verify-email'), [
        ])->layout(Utils::getLayout());
    }
}
