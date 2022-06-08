<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use App\Models\v1\GrantRoleUser;
use App\Models\v1\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $q = request()->get('q');

        $users = User::with('grantrole')->latest()->paginate(5);

        $data = [];

        if ($q) {
            $users = User::with('grantrole')
                ->where('username', 'LIKE', '%' . $q . '%')
                ->orWhere('nama', 'LIKE', '%' . $q . '%')
                ->orWhere('nip', 'LIKE', '%' . $q . '%')
                ->latest()
                ->paginate(5);
        }

        foreach ($users as $u) {
            if ($u->grantrole) {
                $role_name = Role::where('role_id', $u->grantrole->role_id)->first();
            }
            $data[] = (object) [
                'id' => $u->id,
                'nama' => $u->nama,
                'username' => $u->username,
                'nip' => $u->nip,
                'last_login' => $u->last_login,
                'role' => ($u->grantrole) ? $role_name : '',
                'created_at' => $u->created_at,
                'updated_at' => $u->updated_at,
            ];
        }

        return UserResource::collection($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama' => 'required|string',
            'username' => 'required|string|min:5|unique:users,username',
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
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
        $user = User::with('grantrole')->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data pengguna tidak ditemukan.'
            ], 404);
        }

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
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        return new UserResource($data);
    }

    public function update($id, Request $request)
    {
        $user = User::with('grantrole')->find($id);

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

        $role = GrantRoleUser::where('user_id', $id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data pengguna tidak ditemukan.'
            ], 404);
        }

        $user->delete();
        $role->delete();

        return response()->json([
            'status' => true
        ], 204);
    }

    public function role()
    {
        $roles = Role::orderBy('role_id', 'desc')->get();

        return response()->json($roles);
    }

    public function storeRole(Request $request)
    {
        $this->validate($request, [
            'role_name' => 'required|string|unique:roles,role_name'
        ]);

        try {
            $role = Role::create([
                'role_name' => $request->role_name,
            ]);

            if ($role) {
                return response()->json([
                    'status' => true,
                    'message' => 'Role berhasil di tambahkan.'
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messeage' => $e->getMessage(),
            ], 422);
        }
    }

    public function grantRole(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'role_id' => 'required'
        ]);

        $grant = GrantRoleUser::where('user_id', $request->user_id)->first();

        if ($grant === null) {
            GrantRoleUser::create([
                'user_id' => $request->user_id,
                'role_id' => $request->role_id
            ]);
        }

        if ($grant !== null) {
            GrantRoleUser::where('user_id', $request->user_id)
                ->update(['role_id' => $request->role_id]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Role pengguna berhasil di tambahkan.'
        ], 201);
    }

    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::find($request->id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data pengguna tidak ditemukan.'
            ], 404);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password sebelumnya yang anda masukkan tidak valid.'
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil di rubah.'
        ]);
    }
}
