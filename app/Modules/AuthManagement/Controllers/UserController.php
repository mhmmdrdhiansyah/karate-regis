<?php

namespace App\Modules\AuthManagement\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Modules\AuthManagement\Requests\StoreUserRequest;
use App\Modules\AuthManagement\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permission:view users')->only(['index', 'show']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
        $this->middleware('permission:delete users')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $users = User::with('roles')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($request->input('role'), function ($query, $role) {
                $query->whereHas('roles', fn($q) => $q->where('name', $role));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $roles = Role::pluck('name', 'name')->all();

        return view('user.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();

        return view('user.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('auth.users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('user.create', compact('user', 'roles', 'userRole'));
    }

    public function show(User $user)
    {
        return view('user.show', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        $user->syncRoles($request->role);

        return redirect()->route('auth.users.index')
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        if (Auth::id() == $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();

        return redirect()->route('auth.users.index')
            ->with('success', 'User berhasil dihapus');
    }
}
