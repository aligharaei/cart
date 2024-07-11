<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $input): array
    {
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);

        $token = $this->createToken($user);

        return [
            'token' => $token,
            'name'  => $user->name,
        ];
    }

    public function login(array $credentials): array
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $this->createToken($user);

            return [
                'token' => $token,
                'name'  => $user->name,
            ];
        }

        return [];
    }

    private function createToken(Authenticatable|null $user): string
    {
        $user->tokens()->delete();

        return $user->createToken(
            $user->email . '_' . Carbon::now(),
            ['*'],
            Carbon::now()->addDays(6)
        )->plainTextToken;
    }
}
