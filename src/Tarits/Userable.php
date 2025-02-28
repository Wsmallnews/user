<?php

namespace Wsmallnews\User\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Wsmallnews\User\Models\Address;

trait Userable
{
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'user');
    }
}
