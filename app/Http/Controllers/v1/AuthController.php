<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $credentials = $request->only('username', 'password');

        if (!$token = auth()->setTTL((60 * 24))->attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Masukkan username dan password yang valid.'
            ], 422);
        }

        return $this->responseWithToken($token);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout(Request $request)
    {
        auth()->logout();

        return response()->json([], 204);
    }

    protected function responseWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'type' => 'Bearer',
            'data' => auth()->user(),
            'expired_in' => auth()->factory()->getTTL()
        ]);
    }
}
