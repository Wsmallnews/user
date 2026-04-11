<?php

namespace Wsmallnews\User;

use Illuminate\Contracts\Cache\Repository;
use PragmaRX\Google2FA\Google2FA;
use Wsmallnews\User\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Wsmallnews\User\Facades\UserConfig;

class TwoFactorAuthenticationProvider implements TwoFactorAuthenticationProviderContract
{
    /**
     * The underlying library providing two factor authentication helper services.
     *
     * @var Google2FA
     */
    protected $engine;

    /**
     * The cache repository implementation.
     *
     * @var Repository|null
     */
    protected $cache;

    /**
     * Create a new two factor authentication provider instance.
     *
     * @return void
     */
    public function __construct(Google2FA $engine, ?Repository $cache = null)
    {
        $this->engine = $engine;
        $this->cache = $cache;
    }

    /**
     * Generate a new secret key.
     *
     * @return string
     */
    public function generateSecretKey(int $secretLength = 16)
    {
        return $this->engine->generateSecretKey($secretLength);
    }

    /**
     * Get the two factor authentication QR code URL.
     *
     * @param  string  $companyName
     * @param  string  $companyEmail
     * @param  string  $secret
     * @return string
     */
    public function qrCodeUrl($companyName, $companyEmail, $secret)
    {
        return $this->engine->getQRCodeUrl($companyName, $companyEmail, $secret);
    }

    /**
     * Verify the given code.
     *
     * @param  string  $module
     * @param  string  $secret
     * @param  string  $code
     * @return bool
     */
    public function verify($module, $secret, $code)
    {
        if (is_int($customWindow = UserConfig::getConfig($module, 'two_factor.window'))) {
            $this->engine->setWindow($customWindow);
        }

        $timestamp = $this->engine->verifyKeyNewer(
            $secret,
            $code,
            optional($this->cache)->get($key = 'sn-user.2fa_codes.' . md5($code))
        );

        if ($timestamp !== false) {
            if ($timestamp === true) {
                $timestamp = $this->engine->getTimestamp();
            }

            optional($this->cache)->put($key, $timestamp, ($this->engine->getWindow() ?: 1) * 60);

            return true;
        }

        return false;
    }
}
