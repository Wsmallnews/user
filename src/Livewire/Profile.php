<?php

namespace Wsmallnews\User\Livewire;

use Livewire\Attributes\Title;
use Wsmallnews\User\Support\Utils;

class Profile extends Base
{
    #[Title('个人中心')]
    public function render()
    {
        $breadcrumbs = [
            ['label' => '个人中心', 'url' => Utils::route('profile')],
        ];

        return view($this->getViewPath('profile'), [
            'breadcrumbs' => $breadcrumbs,
        ])->layout(Utils::getLayout());
    }
}
