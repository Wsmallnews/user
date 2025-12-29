<?php

namespace Wsmallnews\User;

use Closure;
use Illuminate\Support\Collection;

class AuthsConfig {


    // $auth = [
    //     'guard' => 'web',
    //     'urls' => [
    //         'index' => '/index', 
    //         'login' => '/login',
    //         'logout' => '/logout',
    //     ],
    // ];
    protected ?Collection $auths;


    public function __construct()
    {
        $this->auths = collect();
    }


    public function config($module, array | Closure $auth): self
    {
        $this->auths->put($module, $auth);

        return $this;
    }


    public function getConfig($module, $name = null, $default = null): mixed
    {
        $module = $this->auths->get($module);

        $config = $module instanceof Closure ? $module() : $module;

        return $name ? (data_get($config, $name) ?? $default) : $config;
    }
}
