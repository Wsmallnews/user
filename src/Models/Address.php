<?php

namespace Wsmallnews\User\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Wsmallnews\Support\Models\SupportModel;
use Wsmallnews\User\Enums;

class Address extends SupportModel
{
    protected $table = 'sn_user_addresses';

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',

        'gender' => Enums\Gender::class,
    ];



    /**
     * 所属用户信息
     * 
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}
