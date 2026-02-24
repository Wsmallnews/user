<?php

namespace Wsmallnews\User\Livewire\Settings;

use Livewire\Attributes\Title;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class Password extends Base
{
    #[Title('修改密码')]
    public function render()
    {
        $breadcrumbs = [
            ['label' => '个人中心', 'url' => Utils::route('profile')],
            ['label' => '修改密码', 'url' => Utils::route('settings.password')],
        ];

        return view($this->getViewPath('settings.password'), [
            'breadcrumbs' => $breadcrumbs,
        ])->layout(Utils::getLayout());
    }
}
