<?php

use Wsmallnews\User\Models;

return [
    /**
     * Custom models
     */
    'models' => [
        'user' => \App\Models\User::class,
    ],

    /**
     * auth guard
     */
    'guard' => 'web',

    /**
     * 2FA 配置
     */
    'two_factor' => [
        /**
         * 是否启用双因素认证
         */
        'enabled' => true,

        /**
         * 在启用双因素认证时，必须确认一次，否则启动失败
         * two_factor_confirmed_at: 记录启用确认时间，如果为null, two_factor_secret， two_factor_recovery_codes 会被清空，用户双因素启用失败
         */
        'confirm' => true,

        /**
         * 验证窗口，单位：分钟
         */
        'window' => 1,
    ],

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

    'routes' => [
        /**
         * Whether to enable the cms routes.
         */
        'enabled' => true,
        /**
         * The domain where the cms routes should be registered.
         * If you differentiate tenants by domain, you should set it like this: {tenant:slug}.example.com
         */
        'domain' => null,
        /**
         * the middleware you want to apply on all the cms routes
         * for example if you want to make your cms for users only, add the middleware 'auth'.
         */
        'middleware' => ['web'],
        /**
         * Default path for the blog homepage.
         * If you differentiate tenants by url, you should set it like this: user/{tenant:slug}
         */
        'prefix' => 'user',
        /**
         * Default name prefix for the user routes.
         */
        'name' => 'sn-user.',
        /**
         * default uri for the user routes
         */
        'uri' => [
            'index' => '/',

            'login' => 'login',
            'register' => 'register',
            'profile' => 'profile',
            'forgot-password' => 'forgot-password',
            'reset-password' => 'reset-password/{token}',
            'verify-email' => 'verify-email',
            'verify-email-verification' => 'verify-email/{id}/{hash}',
            'password-confirm' => 'password-confirm',

            // 用户设置
            'settings' => [
                'profile' => 'settings/profile',
                'password' => 'settings/password',
                'two-factor' => 'settings/two-factor',
            ],
        ],
    ],

    'themes' => [
        // 是否启用暗黑模式
        'dark-mode' => true,

        // 默认主题模式
        'default-dark-mode' => 'system',

        // 强制暗黑主题
        'dark-mode-forced' => false,

        'layout' => 'sn-user::components.layouts.app',

        // 页面容器
        'page-container' => 'sn-user::container.page',

        // 视图命名空间
        'view-namespace' => 'sn-user::livewire.',
    ],
];
