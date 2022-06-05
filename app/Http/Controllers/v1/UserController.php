<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $q = request()->get('q');

        $users = User::latest()->paginate(5);

        if ($q) {
            $users = User::where('username', 'LIKE', '%' . $q . '%')
                ->orWhere('nama', 'LIKE', '%' . $q . '%')
                ->orWhere('nip', 'LIKE', '%' . $q . '%')
                ->latest()
                ->paginate(5);
        }

        return UserResource::collection($users);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama' => 'required|string',
            'username' => 'required|string|min:5|unique:users,username',
            'password' => ['required', 'string', Password::defaults()],
            'nip' => 'required|string|unique:users,nip',
        ]);

        try {
            $user = User::create([
                'nama' => $request->nama,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nip' => $request->nip
            ]);

            if ($user) {
                return response()->json([
                    'status' => true,
                    'message' => 'Data pengguna berhasil ditambahkan.',
                    'data' => new UserResource($user)
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messeage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data pengguna tidak ditemukan.'
            ], 404);
        }

        return new UserResource($user);
    }

    public function update($id, Request $request)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data pengguna tidak ditemukan.'
            ], 404);
        }

        $user->nama = $request->nama;
        $user->nip = $request->nip;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Data pengguna berhasil diupdate.'
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data pengguna tidak ditemukan.'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => true
        ], 204);
    }
}
