<?php

namespace Wsmallnews\User\Livewire;

use Wsmallnews\User\Support\Utils;

class Profile extends Base
{
    public function render()
    {
        $breadcrumbs = [
            ['label' => __('sn-user::user.titles.profile'), 'url' => Utils::route('profile')],
        ];

        return view($this->getViewPath('profile'), [
            'breadcrumbs' => $breadcrumbs,
        ])->layout(Utils::getLayout())->title(__('sn-user::user.titles.profile'));
    }
}
