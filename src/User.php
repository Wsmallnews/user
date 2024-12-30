<?php

namespace Wsmallnews\User;

class User 
{

    /**
     * The user model that should be used by Jetstream.
     *
     * @var string
     */
    public static $userModel = 'App\\Models\\User';


    public static $routeNames = [];


    /**
     * Get the name of the user model used by the application.
     *
     * @return string
     */
    public static function userModel()
    {
        return static::$userModel;
    }

    /**
     * Get a new instance of the user model.
     *
     * @return mixed
     */
    public static function newUserModel()
    {
        $model = static::userModel();

        return new $model;
    }



    public static function routeNames($name = null)
    {
        if ($name) {
            return static::$routeNames[$name] ?? null;
        }

        return static::$routeNames;
    }


    
}
