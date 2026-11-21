<?php

namespace Wsmallnews\User\Models\Concerns;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Wsmallnews\Support\Features\Site\Site;
use Wsmallnews\User\Contracts\TwoFactorAuthenticationProvider;
use Wsmallnews\User\Events\RecoveryCodeReplaced;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\RecoveryCode;

trait TwoFactorAuthenticatable
{
    /**
     * Determine if two-factor authentication has been enabled.
     */
    public function hasEnabledTwoFactorAuthentication(Site $site)
    {
        if (UserConfig::confirmsTwoFactorAuthentication($site)) {
            return ! is_null($this->two_factor_secret) &&
                   ! is_null($this->two_factor_confirmed_at);
        }

        return ! is_null($this->two_factor_secret);
    }

    /**
     * Get the user's two factor authentication recovery codes.
     */
    public function recoveryCodes(Site $site)
    {
        return json_decode(UserConfig::currentEncrypter()->decrypt($this->two_factor_recovery_codes), true);
    }

    /**
     * Replace the given recovery code with a new one in the user's stored codes.
     */
    public function replaceRecoveryCode(Site $site, $code)
    {
        $this->forceFill([
            'two_factor_recovery_codes' => UserConfig::currentEncrypter()->encrypt(str_replace(
                $code,
                RecoveryCode::generate(),
                UserConfig::currentEncrypter()->decrypt($this->two_factor_recovery_codes)
            )),
        ])->save();

        RecoveryCodeReplaced::dispatch($this, $code);
    }

    /**
     * Get the QR code SVG of the user's two factor authentication QR code URL.
     */
    public function twoFactorQrCodeSvg(Site $site)
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(192, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
                new SvgImageBackEnd
            )
        ))->writeString($this->twoFactorQrCodeUrl($site));

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    /**
     * Get the two factor authentication QR code URL.
     */
    public function twoFactorQrCodeUrl(Site $site)
    {
        return app(TwoFactorAuthenticationProvider::class)->qrCodeUrl(
            config('app.name'),
            $this->email,
            UserConfig::currentEncrypter()->decrypt($this->two_factor_secret)
        );
    }
}
