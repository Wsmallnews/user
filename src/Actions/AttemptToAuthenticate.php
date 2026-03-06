<?php

namespace Wsmallnews\User\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Wsmallnews\User\User;

class AttemptToAuthenticate
{

    public function __construct($formData, $module)
    {
        $this->formData = $formData;
        $this->guard = UserConfig::getConfig($this->module, 'guard');
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


    public function validateCredentials($user)
    {
        $credentials = $this->getCredentials();

        if (! $user || ! $authProvider->validateCredentials($user, $credentials)) {
            return false;
        }

        return true;
    }


    public function finishLogin($user)
    {
        Auth::guard($this->guard)->login($user, $formData['remember'] ?? false);

        // 登录成功，重新生成 session id
        Session::regenerate();
    }



    /**
     * Get the credentials from the input.
     *
     * @param  array  $input
     * @return array
     */
    protected function getCredentials()
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
