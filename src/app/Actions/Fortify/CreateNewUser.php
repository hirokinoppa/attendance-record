<?php

namespace App\Actions\Fortify;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * 新規ユーザーを作成する
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        $request = new RegisterRequest();

        Validator::make(
            $input,
            $request->rules(),
            $request->messages()
        )->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => 'general',
        ]);
    }
}