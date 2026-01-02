<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    // Menampilkan daftar Role
    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            // 1. Logika Search
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            })
            // 2. Urutkan Terbaru
            ->latest()
            // 3. Pagination & Query String (agar search tidak hilang saat pindah hal)
            ->paginate(10)
            ->withQueryString();

        return view('role.index', compact('roles'));
    }

    // Form Create (Reuse view create)
    public function create()
    {
        // Ambil semua permission untuk checkbox
        $permissions = Permission::all();
        return view('role.create', compact('permissions'));
    }

    // Simpan Role Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array' // Wajib pilih minimal 1 permission
        ]);

        DB::transaction(function () use ($request) {
            $role = Role::create(['name' => $request->name]);
            $role->syncPermissions($request->permissions);
        });

        return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat');
    }

    // Form Edit (Reuse view create)
    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        // Proteksi: Super Admin tidak boleh diedit namanya (bahaya)
        if ($role->name == 'super-admin') {
            return back()->with('error', 'Role Super Admin tidak bisa diedit!');
        }

        $permissions = Permission::all();

        // Ambil ID permission yang sudah dimiliki role ini
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('role.create', compact('role', 'permissions', 'rolePermissions'));
    }

    // Update Role
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($role->name == 'super-admin') {
            return back()->with('error', 'Restricted!');
        }

        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'required|array'
        ]);

        DB::transaction(function () use ($request, $role) {
            $role->update(['name' => $request->name]);
            $role->syncPermissions($request->permissions);
        });

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui');
    }

    // Hapus Role
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->name == 'super-admin') {
            return back()->with('error', 'Role Super Admin tidak boleh dihapus!');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus');
    }
}
