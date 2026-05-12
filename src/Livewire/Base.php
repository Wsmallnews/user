<?php

namespace Wsmallnews\User\Livewire;

use Wsmallnews\Support\Livewire\Base as BaseComponent;
use Wsmallnews\User\Support\Utils;

class Base extends BaseComponent
{
    public function getViewPath($name): string
    {
        return Utils::getViewNamespace() . $name;
    }

    public function getPageContainer(): string
    {
        return Utils::getPageContainer();
    }
}
