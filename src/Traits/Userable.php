<?php

namespace Wsmallnews\User\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Wsmallnews\Order\Models\Order;
use Wsmallnews\Order\Models\OrderItem;
use Wsmallnews\User\Models\Address;

trait Userable
{
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'user');
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'buyer');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'buyer');
    }
}
