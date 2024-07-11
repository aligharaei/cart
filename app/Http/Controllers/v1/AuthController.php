<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register api
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $input = $request->validated();

        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
//Todo add local carts
        $success['token'] = $this->createToken($user);
        $success['name'] = $user->name;

        return response()->json($success);
    }

    /**
     * Login api
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            $success['token'] = $this->createToken($user);
            $success['name'] = $user->name;
//Todo add local carts
            return response()->json([
                'data'    => $success,
                'message' => 'Successfully Logged in'
            ]);

        } else {
            return response()->json(['message' => 'Cannot login due to an unknown error'], 500);
        }
    }

    private function createToken(User|Authenticatable|null $user)
    {
        $user->tokens()->delete();

        return $user->createToken(
            $user->email . '_' . Carbon::now(),
            ['*'],
            Carbon::now()->addDays(6)
        )->plainTextToken;
    }
}
