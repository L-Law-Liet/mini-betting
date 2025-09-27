<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $cred = $request->validated();
        if(!Auth::attempt($cred))
            return response()->json(['message'=>'Invalid credentials'],401);

        $token = $request->user()->createToken('api')->plainTextToken;
        return response()->json([
            'token' => $token,
            'user' => $request->user()
        ]);
    }
}
