<?php

namespace Wsmallnews\User;

use Closure;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Wsmallnews\Support\Features\Site\Concerns\AssertsSiteOwnership;
use Wsmallnews\Support\Features\Site\Site;

class UserConfig
{
    use AssertsSiteOwnership;

    /** Per-site registrations: module => siteId => auth array|closure. */
    protected ?Collection $configs;

    public function __construct()
    {
        $this->configs = collect();
    }

    /** Register auth config for one site (values may be a closure resolved per call). */
    public function config(string $module, array | Closure $auth, Site $site): self
    {
        $this->assertOwnership($module, $site);

        $sites = $this->configs->get($module, collect());
        $sites->put($site->id(), $auth);
        $this->configs->put($module, $sites);

        return $this;
    }

    /** Resolve the auth entry of the given site; the module is the site's owner. */
    protected function resolveAuth(Site $site): array | Closure | null
    {
        return $this->configs->get($this->siteModule($site), collect())->get($site->id());
    }

    /**
     * 获取设置
     *
     * @param  Site|null  $site  当前站点；无站点上下文时为 null（只用 sn-user 兜底配置）
     * @param  string  $name  设置项名称
     * @param  mixed  $default  默认值
     * @param  array  $params  配置动态参数
     * @param  array  $fieldParams  字段参数
     */
    public function getConfig(?Site $site, $name = null, $default = null, $params = [], $fieldParams = []): mixed
    {
        $auth = $site === null ? null : $this->resolveAuth($site);

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
     */
    public function confirmsTwoFactorAuthentication(?Site $site): bool
    {
        return $this->getConfig($site, 'two_factor.enabled', false) && $this->getConfig($site, 'two_factor.confirm', false);
    }

    /**
     * Encrypter for the 2FA secrets.
     *
     * The users table is shared by every site, so the key must be deployment-wide:
     * a per-site encrypter would make one site's secrets unreadable in another.
     * Site-level `encrypter` config is deliberately ignored.
     */
    public function currentEncrypter(): Encrypter
    {
        return Model::$encrypter ?? Crypt::getFacadeRoot();
    }
}
