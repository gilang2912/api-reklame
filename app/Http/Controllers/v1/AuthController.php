<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        $user = User::where('username', '=', $request->username)->first();
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        return $this->responseWithToken($token);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([], 204);
    }

    protected function responseWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'type' => 'Bearer',
            'user' => auth()->user(),
            'expired_in' => auth()->factory()->getTTL()
        ]);
    }
}
