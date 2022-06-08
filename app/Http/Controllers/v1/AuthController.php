<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\v1\Role;
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
        $user = User::with('grantrole')->find(auth()->user()->id);

        if ($user->grantrole) {
            $role_name = Role::where('role_id', $user->grantrole->role_id)->first();
        }

        $data = (object) [
            'id' => $user->id,
            'nama' => $user->nama,
            'username' => $user->username,
            'nip' => $user->nip,
            'last_login' => $user->last_login,
            'role' => ($user->grantrole) ? $role_name : '',
        ];

        return response()->json($data);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([], 204);
    }

    protected function responseWithToken($token)
    {
        $user = User::with('grantrole')->find(auth()->user()->id);

        if ($user->grantrole) {
            $role_name = Role::where('role_id', $user->grantrole->role_id)->first();
        }

        $data = (object) [
            'id' => $user->id,
            'nama' => $user->nama,
            'username' => $user->username,
            'nip' => $user->nip,
            'last_login' => $user->last_login,
            'role' => ($user->grantrole) ? $role_name : '',
        ];

        return response()->json([
            'token' => $token,
            'type' => 'Bearer',
            'user' => $data,
            'expired_in' => auth()->factory()->getTTL()
        ]);
    }
}
