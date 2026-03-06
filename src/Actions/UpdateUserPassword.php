<?php

namespace Wsmallnews\User\Actions;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Wsmallnews\User\User;

class UpdateUserPassword
{
    /**
     * Update the user's password.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function __invoke($user, array $formData)
    {
        $user->update([
            'password' => $formData['password'],
        ]);
    }
}
