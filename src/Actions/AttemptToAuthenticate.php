<?php

namespace Wsmallnews\User\Actions;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Wsmallnews\User\Facades\UserConfig;

class AttemptToAuthenticate
{

    protected string $module;

    protected array $formData;
    
    protected string $guard;

    protected string $throttleKey;


    /**
      * AttemptToAuthenticate constructor.
      *
      * @param  string  $module
      * @param  array  $formData
      */
    public function __construct(string $module, array $formData)
    {
        $this->module = $module;
        $this->formData = $formData;
        $this->guard = UserConfig::getConfig($module, 'guard');

        $this->throttleKey = $this->throttleKey($formData);
    }


    /**
     * Ensure the authentication request is not rate limited.
     *
     * @return bool
     */
    public function ensureIsNotRateLimited(): bool
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey, 5)) {
            return true;
        }

        event(new Lockout(request()));
        return false;
    }


     /**
     * Lock out the user.
     *
     * @return int
     */
    public function lockSecond(): int
    {
        return RateLimiter::availableIn($this->throttleKey);
    }


    /**
     * Attempt to authenticate the user.
     *
     * @return mixed
     */
    public function retrieveUser()
    {
        $credentials = $this->getCredentials();

        /** @var SessionGuard $authGuard */
        $authGuard = Auth::guard($this->guard);
        $authProvider = $authGuard->getProvider();      /** @phpstan-ignore-line */

        // 当前 user model 实例
        return $authProvider->retrieveByCredentials($credentials);
    }



     /**
     * Validate the user's credentials.
     *
     * @param  mixed  $user
     * @return bool
     */
    public function validateCredentials($user): bool
    {
        $credentials = $this->getCredentials();

        /** @var SessionGuard $authGuard */
        $authGuard = Auth::guard($this->guard);
        $authProvider = $authGuard->getProvider();      /** @phpstan-ignore-line */

        if (! $user || ! $authProvider->validateCredentials($user, $credentials)) {
            RateLimiter::hit($this->throttleKey);

            return false;
        }

        return true;
    }


     /**
     * Log the user in.
     *
     * @param  mixed  $user
     * @return void
     */
    public function finishLogin($user): void
    {
        Auth::guard($this->guard)->login($user, $this->formData['remember'] ?? false);

        RateLimiter::clear($this->throttleKey);

        // 登录成功，重新生成 session id
        Session::regenerate();
    }


    /**
     * Get the authentication rate limiting throttle key.
     * 
     * @param  array  $formData
     * @return string
     */
    protected function throttleKey($formData): string
    {
        return Str::transliterate(Str::lower($formData['account']) . '|' . request()->ip());
    }

    /**
     * Get the credentials from the input.
     *
     * @return array
     */
    protected function getCredentials(): array
    {
        $credentials = Arr::only($this->formData, ['password']);
        $credentials['account'] = function ($query) {
            $query->where(function ($query) {
                $query->where('email', $this->formData['account'])
                    ->orWhere('mobile', $this->formData['account']);
            });
        };

        return $credentials;
    }
}
