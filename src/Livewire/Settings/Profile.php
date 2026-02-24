<?php

namespace Wsmallnews\User\Livewire\Settings;

use Livewire\Attributes\Title;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class Profile extends Base
{
    #[Title('修改资料')]
    public function render()
    {
        $breadcrumbs = [
            ['label' => '个人中心', 'url' => Utils::route('profile')],
            ['label' => '修改资料', 'url' => Utils::route('settings.profile')],
        ];

        return view($this->getViewPath('settings.profile'), [
            'breadcrumbs' => $breadcrumbs,
        ])->layout(Utils::getLayout());
    }
}
