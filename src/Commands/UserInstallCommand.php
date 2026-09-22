<?php

namespace Wsmallnews\User\Commands;

use Wsmallnews\Support\Commands\PackageInstallCommand;

class UserInstallCommand extends PackageInstallCommand
{
    protected string $packageName = 'sn-user';
}
