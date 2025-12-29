<?php

return [

    // 'actions' => [
    //     'create_new_user' => Wsmallnews\User\Actions\Fortify\CreateNewUser::class,
    //     'reset_user_password' => Wsmallnews\User\Actions\Fortify\ResetUserPassword::class,
    //     'update_user_password' => Wsmallnews\User\Actions\Fortify\UpdateUserPassword::class,
    //     'update_user_profile_information' => Wsmallnews\User\Actions\Fortify\UpdateUserProfileInformation::class,
    //     'redirect_if_two_factor_authenticatable' => Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable::class,
    // ],

    /**
     * 文件基础目录，会自动拼接当前年月日 (仅用于 filament 默认上传组件 (Forms\Components\FileUpload))
     */
    'file_directory' => 'sn/user/',
];
