<?php

namespace Wsmallnews\User;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Wsmallnews\User\Enums\Gender;
use Wsmallnews\User\Enums\Status;

trait Userable
{

    /**
     * gender cast
     */
    protected function genderIcon(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Gender::from($attributes['gender'])?->getIcon(),
            set: fn ($value) => $value,
        );
    }


    /**
     * status cast
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Status::from($value),
            set: fn ($value) => $value,
        );
    }
}
