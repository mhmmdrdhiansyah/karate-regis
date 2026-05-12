<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;

use App\Http\Requests\StoreKontingenUserRequest;
use App\Http\Requests\UpdateKontingenRequest;
use App\Models\Contingent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KontingenManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view kontingen')->only(['index', 'show']);
        $this->middleware('permission:create kontingen')->only(['create', 'store']);
        $this->middleware('permission:edit kontingen')->only(['edit', 'update']);
        $this->middleware('permission:delete kontingen')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $perPage = $request->input('per_page', 10);
        $showTrashed = $request->input('trashed') === 'only';

        $contingents = Contingent::with('user')
            ->when($showTrashed, function ($query) {
                $query->onlyTrashed();
            })
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('official_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('regency', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.kontingen.index', compact('contingents', 'showTrashed'));
    }

    public function create(): View
    {
        return view('admin.kontingen.create');
    }

    public function store(StoreKontingenUserRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('kontingen');

            $user->contingent()->create([
                'name' => $request->contingent_name,
                'official_name' => $request->official_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'province' => $request->province,
                'regency' => $request->regency,
            ]);
        });

        return redirect()->route('kontingen.index')
            ->with('success', 'Kontingen berhasil ditambahkan');
    }

    public function show(Contingent $kontingen): View
    {
        $kontingen->load('user');

        return view('admin.kontingen.show', compact('kontingen'));
    }

    public function edit(Contingent $kontingen): View
    {
        $kontingen->load('user');

        return view('admin.kontingen.edit', compact('kontingen'));
    }

    public function update(UpdateKontingenRequest $request, Contingent $kontingen): RedirectResponse
    {
        $kontingen->update([
            'name' => $request->contingent_name,
            'official_name' => $request->official_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'province' => $request->province,
            'regency' => $request->regency,
        ]);

        $userData = $request->only('name', 'username', 'email');
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $kontingen->user->update($userData);

        return redirect()->route('kontingen.index')
            ->with('success', 'Data kontingen berhasil diperbarui');
    }

    public function destroy(Contingent $kontingen): RedirectResponse
    {
        DB::transaction(function () use ($kontingen) {
            $kontingen->delete();
            $kontingen->user->delete();
        });

        return redirect()->route('kontingen.index')
            ->with('success', 'Kontingen berhasil dihapus');
    }

    public function restore($id)
    {
        DB::transaction(function () use ($id) {
            $kontingen = Contingent::withTrashed()->findOrFail($id);
            $kontingen->restore();
            if ($kontingen->user_id) {
                User::withTrashed()->find($kontingen->user_id)?->restore();
            }
        });

        return redirect()->back()->with('success', 'Kontingen berhasil dipulihkan');
    }

    public function forceDelete($id)
    {
        DB::transaction(function () use ($id) {
            $kontingen = Contingent::withTrashed()->findOrFail($id);
            $user = $kontingen->user_id ? User::withTrashed()->find($kontingen->user_id) : null;
            
            // 1. Ambil ID participant dan payment untuk hapus data relasi
            $participantIds = $kontingen->participants()->pluck('id');
            $paymentIds = $kontingen->payments()->pluck('id');

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
            $kontingen->drafts()->delete();

            // 5. Hapus Team Groups
            $kontingen->teamGroups()->delete();

            // 6. Hapus Participants & Payments
            $kontingen->participants()->delete();
            $kontingen->payments()->delete();

            // 7. Hapus Kontingen & User
            $kontingen->forceDelete();
            $user?->forceDelete();
        });

        return redirect()->back()->with('success', 'Kontingen berhasil dihapus permanen');
    }
}
