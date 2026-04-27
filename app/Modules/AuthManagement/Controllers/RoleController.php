<?php

namespace App\Modules\AuthManagement\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Modules\AuthManagement\Requests\StoreRoleRequest;
use App\Modules\AuthManagement\Requests\UpdateRoleRequest;
use App\Modules\AuthManagement\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permission:view roles')->only(['index', 'show']);
        $this->middleware('permission:create roles')->only(['create', 'store']);
        $this->middleware('permission:edit roles')->only(['edit', 'update']);
        $this->middleware('permission:delete roles')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('role.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();

        return view('role.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        DB::transaction(function () use ($request) {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web'
            ]);
            $role->syncPermissions($request->permissions);
        });

        return redirect()->route('auth.roles.index')
            ->with('success', 'Role baru berhasil dibuat');
    }

    public function edit(Role $role)
    {
        if ($role->name == 'super-admin') {
            return back()->with('error', 'Role Super Admin tidak bisa diedit!');
        }

        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('role.create', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        if ($role->name == 'super-admin') {
            return back()->with('error', 'Restricted!');
        }

        DB::transaction(function () use ($request, $role) {
            $role->update(['name' => $request->name]);
            $role->syncPermissions($request->permissions);
        });

        return redirect()->route('auth.roles.index')
            ->with('success', 'Role berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        if ($role->name == 'super-admin') {
            return back()->with('error', 'Role Super Admin tidak boleh dihapus!');
        }

        $role->delete();

        return redirect()->route('auth.roles.index')
            ->with('success', 'Role berhasil dihapus');
    }
}
