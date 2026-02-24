<?php

namespace Wsmallnews\User\Livewire;

use Livewire\Component;
use Wsmallnews\User\Support\Utils;

class Base extends Component
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
