<?php

namespace Wsmallnews\User\Livewire\Settings;

use Livewire\Attributes\Title;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class TwoFactor extends Base
{
    #[Title('双因素身份验证')]
    public function render()
    {
        $breadcrumbs = [
            ['label' => '个人中心', 'url' => Utils::route('profile')],
            ['label' => '双因素身份验证', 'url' => Utils::route('settings.two-factor')],
        ];

        return view($this->getViewPath('settings.two-factor'), [
            'breadcrumbs' => $breadcrumbs,
        ])->layout(Utils::getLayout());
    }
}
