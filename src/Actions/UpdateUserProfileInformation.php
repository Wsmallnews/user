<?php

namespace Wsmallnews\User\Actions;

use App\Models\User;

class UpdateUserProfileInformation
{
    /**
     * Update the user's profile information.
     *
     * @param  mixed  $user
     */
    public function __invoke(User $user, array $formData): void
    {
        $user->fill($formData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }
}
