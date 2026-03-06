<?php

namespace Wsmallnews\User\Actions;

use App\Models\User as UserModel;
use Illuminate\Support\Facades\Hash;
use Wsmallnews\User\Enums\Gender;
use Wsmallnews\User\Support\Utils;

class CreateNewUser
{
    /**
     * Create a new user instance.
     */
    public function __invoke(array $formData): UserModel
    {
        $userModel = Utils::getUserModel();

        $data = [
            'name' => $formData['name'],
            'email' => $formData['email'],
            'password' => Hash::make($formData['password']),
            'gender' => $formData['gender'] ?? Gender::Undisclosed,
        ];

        return $userModel::create($data);
    }
}
