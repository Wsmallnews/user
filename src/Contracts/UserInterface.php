<?php

namespace Wsmallnews\User\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * 用户
 */
interface UserInterface
{
    public function addresses(): MorphMany;
}
