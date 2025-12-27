<?php

return [

    'actions' => [
        'create_new_user' => Wsmallnews\User\Actions\Fortify\CreateNewUser::class,
        'reset_user_password' => Wsmallnews\User\Actions\Fortify\ResetUserPassword::class,
        'update_user_password' => Wsmallnews\User\Actions\Fortify\UpdateUserPassword::class,
        'update_user_profile_information' => Wsmallnews\User\Actions\Fortify\UpdateUserProfileInformation::class,
        'redirect_if_two_factor_authenticatable' => Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable::class,
    ],

];
