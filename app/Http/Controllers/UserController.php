<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Routing\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct()
    {
        // 1. Batasi akses 'create' dan 'store' hanya untuk yg punya izin 'create users'
        $this->middleware('permission:create users')->only(['create', 'store']);

        // 2. Batasi akses 'edit' dan 'update' hanya untuk yg punya izin 'edit users'
        $this->middleware('permission:edit users')->only(['edit', 'update']);

        // 3. Batasi akses 'destroy' hanya untuk yg punya izin 'delete users'
        $this->middleware('permission:delete users')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $users = User::with('roles')
            // 1. Logika Pencarian
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            // 2. Urutkan terbaru
            ->latest()
            // 3. Pagination 10 per halaman
            ->paginate(10)
            // 4. Pastikan search terbawa saat klik next page
            ->withQueryString();

        return view('user.index', compact('users'));
    }

    public function show(string $id)
    {
        $user = User::with('roles')->findOrFail($id);

        return view('user.show', compact('user'));
    }

    public function create()
    {
        // Ambil semua role untuk dropdown
        $roles = Role::pluck('name', 'name')->all();
        return view('user.create', compact('roles'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required']
        ]);

        // 2. Buat User Baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3. Assign Role
        $user->assignRole($request->role);

        // 4. Redirect kembali ke index dengan pesan sukses
        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::pluck('name', 'name')->all();

        // Ambil role user saat ini untuk keperluan select option
        $userRole = $user->roles->pluck('name', 'name')->all();

        // PERUBAHAN DISINI: Kita arahkan ke 'user.create'
        // Kita kirimkan variabel $user, $roles, dan $userRole
        return view('user.create', compact('user', 'roles', 'userRole'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'role' => ['required']
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update role
        $user->syncRoles($request->role);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // FIX: Langsung panggil id() dari helper auth()
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus');
    }
}
