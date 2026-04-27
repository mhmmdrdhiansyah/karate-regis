<?php

namespace App\Modules\AuthManagement\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Modules\AuthManagement\Requests\StorePermissionRequest;
use App\Modules\AuthManagement\Requests\UpdatePermissionRequest;
use App\Modules\AuthManagement\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permission:view permissions')->only(['index', 'show']);
        $this->middleware('permission:create permissions')->only(['create', 'store']);
        $this->middleware('permission:edit permissions')->only(['edit', 'update']);
        $this->middleware('permission:delete permissions')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $permissions = Permission::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('permission.index', compact('permissions'));
    }

    public function create()
    {
        return view('permission.create');
    }

    public function store(StorePermissionRequest $request)
    {
        Permission::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        return redirect()->route('auth.permissions.index')
            ->with('success', 'Permission baru berhasil dibuat');
    }

    public function edit(Permission $permission)
    {
        return view('permission.create', compact('permission'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $permission->update(['name' => $request->name]);

        return redirect()->route('auth.permissions.index')
            ->with('success', 'Permission berhasil diperbarui');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('auth.permissions.index')
            ->with('success', 'Permission berhasil dihapus');
    }
}
