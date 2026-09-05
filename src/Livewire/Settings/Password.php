<?php

namespace Wsmallnews\User\Livewire\Settings;

use Wsmallnews\Support\Facades\Seo;
use Wsmallnews\User\Livewire\Base;
use Wsmallnews\User\Support\Utils;

class Password extends Base
{
    public function render()
    {
        Seo::title(__('sn-user::user.titles.settings_password'))->robots('noindex');
        $breadcrumbs = [
            ['label' => __('sn-user::user.titles.profile'), 'url' => Utils::route('profile')],
            ['label' => __('sn-user::user.titles.settings_password'), 'url' => Utils::route('settings.password')],
        ];

        return view($this->getViewPath('settings.password'), [
            'breadcrumbs' => $breadcrumbs,
        ])->layout(Utils::getLayout());
    }
}
