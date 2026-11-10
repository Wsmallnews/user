<?php

namespace Wsmallnews\User;

use Closure;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Wsmallnews\Support\Features\Site\Site;

class UserConfig
{
    /** Per-site registrations: module => siteId => auth array|closure. */
    protected ?Collection $configs;

    public function __construct()
    {
        $this->configs = collect();
    }

    /** Register auth config for one site (values may be a closure resolved per call). */
    public function config(string $module, array | Closure $auth, Site $site): self
    {
        $sites = $this->configs->get($module, collect());
        $sites->put($site->id(), $auth);
        $this->configs->put($module, $sites);

        return $this;
    }

    /** Resolve the auth entry of the given site. */
    protected function resolveAuth($module, Site $site): array | Closure | null
    {
        return $this->configs->get($module, collect())->get($site->id());
    }

    /**
     * 获取设置
     *
     * @param  string  $module  所属模块
     * @param  Site  $site  站点必传；无站点上下文的场景由调用方以 SiteContext::run 显式绑定后传入
     * @param  string  $name  设置项名称
     * @param  mixed  $default  默认值
     * @param  array  $params  配置动态参数
     * @param  array  $fieldParams  字段参数
     */
    public function getConfig(string $module, Site $site, $name = null, $default = null, $params = [], $fieldParams = []): mixed
    {
        $auth = $this->resolveAuth($module, $site);

        $config = $auth instanceof Closure ? $auth($params) : $auth;

        if ($name) {
            // 站点分区 > user 包兜底配置（如 sn-user.two_factor.*）> 调用方默认
            $nameValue = data_get($config, $name) ?? config("sn-user.{$name}") ?? $default;
            if ($nameValue instanceof Closure) {
                $nameValue = $nameValue($fieldParams);
            }

            return $nameValue;
        }

        return $config;
    }

    /**
     * 确认是否开启了双因素认证
     *
     * @param  string  $module  所属模块
     */
    public function confirmsTwoFactorAuthentication($module, Site $site): bool
    {
        return $this->getConfig($module, $site, 'two_factor.enabled', false) && $this->getConfig($module, $site, 'two_factor.confirm', false);
    }

    /**
     * Get the current encrypter being used by the model.
     */
    public function currentEncrypter(string $module, Site $site): Encrypter
    {
        return $this->getConfig($module, $site, 'encrypter') ?? Model::$encrypter ?? Crypt::getFacadeRoot();
    }
}
