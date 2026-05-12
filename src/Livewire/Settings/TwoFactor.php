<?php

namespace Wsmallnews\User\Livewire\Settings;

use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class TwoFactor extends Base
{
    public function render()
    {
        $breadcrumbs = [
            ['label' => __('sn-user::user.titles.profile'), 'url' => Utils::route('profile')],
            ['label' => __('sn-user::user.titles.settings_two_factor'), 'url' => Utils::route('settings.two-factor')],
        ];

        return view($this->getViewPath('settings.two-factor'), [
            'breadcrumbs' => $breadcrumbs,
        ])->layout(Utils::getLayout())->title(__('sn-user::user.titles.settings_two_factor'));
    }
}
