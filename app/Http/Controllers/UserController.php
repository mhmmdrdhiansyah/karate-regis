<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
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
        $showTrashed = $request->input('trashed') === 'only';

        $users = User::with('roles')
            ->when($showTrashed, function ($query) {
                $query->onlyTrashed();
            })
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

        return view('user.index', compact('users', 'roles', 'showTrashed'));
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
            'password' => Hash::make($request->password),
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
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password'], $data['password_confirmation']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update(Arr::except($data, ['role', 'password_confirmation']));
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

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->back()->with('success', 'User berhasil dipulihkan');
    }

    public function forceDelete($id)
    {
        DB::transaction(function () use ($id) {
            $user = User::withTrashed()->findOrFail($id);
            
            $contingent = $user->contingent()->withTrashed()->first();
            if ($contingent) {
                // 1. Ambil ID participant dan payment untuk hapus data relasi
                $participantIds = $contingent->participants()->pluck('id');
                $paymentIds = $contingent->payments()->pluck('id');

                // 2. Hapus Results yang menempel ke Registrations
                DB::table('results')->whereIn('registration_id', function($q) use ($participantIds, $paymentIds) {
                    $q->select('id')->from('registrations')
                        ->whereIn('participant_id', $participantIds)
                        ->orWhereIn('payment_id', $paymentIds);
                })->delete();

                // 3. Hapus Registrations
                DB::table('registrations')->whereIn('participant_id', $participantIds)
                    ->orWhereIn('payment_id', $paymentIds)
                    ->delete();

                // 4. Hapus Drafts (items akan cascade delete dari DB)
                $contingent->drafts()->delete();

                // 5. Hapus Team Groups
                $contingent->teamGroups()->delete();

                // 6. Hapus Participants & Payments
                $contingent->participants()->delete();
                $contingent->payments()->delete();

                // 7. Hapus Kontingen
                $contingent->forceDelete();
            }
            
            $user->forceDelete();
        });

        return redirect()->back()->with('success', 'User berhasil dihapus permanen');
    }
}
