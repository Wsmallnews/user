<?php

namespace Wsmallnews\User\Livewire\Settings;

use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class Profile extends Base
{
    public function render()
    {
        $breadcrumbs = [
            ['label' => __('sn-user::user.titles.profile'), 'url' => Utils::route('profile')],
            ['label' => __('sn-user::user.titles.settings_profile'), 'url' => Utils::route('settings.profile')],
        ];

        return view($this->getViewPath('settings.profile'), [
            'breadcrumbs' => $breadcrumbs,
        ])->layout(Utils::getLayout())->title(__('sn-user::user.titles.settings_profile'));
    }
}
