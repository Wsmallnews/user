<?php

use App\Models\User;
use Wsmallnews\User\Filament\Resources\Users\UserResource;
use Wsmallnews\User\Models;

return [
    /**
     * Custom models
     */
    'models' => [
        'user' => User::class,
    ],

    /**
     * 本包组件不消费 scope：引入无需声明 scopeable。
     *
     * 资源清单（默认解开即注册；内存注入 resources([...]) 对同类覆盖）。
     */
    /**
     * 2FA 配置（站点 / 使用方模块未声明时的兜底）
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

    'file_directory' => 'sn/user/',
];
