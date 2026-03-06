<?php

namespace Wsmallnews\User\Actions;

use App\Models\User;

class UpdateUserPassword
{
    /**
     * Update the user's password.
     *
     * @param  mixed  $user
     */
    public function __invoke(User $user, array $formData): void
    {
        $user->update([
            'password' => $formData['password'],
        ]);
    }
}
