<?php

namespace Wsmallnews\User\Livewire\Auth;

use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class VerifyEmail extends Base
{
    public string $type = 'check';

    public function mount()
    {
        $this->type = request()->query('type', 'check');
    }

    public function render()
    {
        return view($this->getViewPath('auth.verify-email'), [])
            ->layout(Utils::getLayout())
            ->title(__('sn-user::user.titles.verify_email'));
    }
}
