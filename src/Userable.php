<?php

namespace Wsmallnews\User;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Wsmallnews\User\Enums\Status;

trait Userable
{

    /**
     * status cast
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Status::from($value),
            set: fn($value) => $value,
        );
    }
}
