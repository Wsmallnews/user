<?php

namespace Wsmallnews\User\Actions;

class UpdateUserProfileInformation
{
    /**
     * Update the user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $formData
     * @return 
     */
    public function __invoke($user, array $formData)
    {
        $user->fill($formData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }
}
