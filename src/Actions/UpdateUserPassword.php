<?php

namespace Wsmallnews\User\Actions;

class UpdateUserPassword
{
    /**
     * Update the user's password.
     *
     * @param  mixed  $user
     * @param  array  $formData
     * @return void
     */
    public function __invoke($user, array $formData)
    {
        $user->update([
            'password' => $formData['password'],
        ]);
    }
}
