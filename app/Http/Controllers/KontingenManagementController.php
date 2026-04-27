<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKontingenUserRequest;
use App\Http\Requests\UpdateKontingenRequest;
use App\Models\Contingent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

    public function index(): View
    {
        $contingents = Contingent::with('user')->latest()->paginate(10);

        return view('admin.kontingen.index', compact('contingents'));
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
                'password' => bcrypt($request->password),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('kontingen');

            $user->contingent()->create([
                'name' => $request->contingent_name,
                'official_name' => $request->official_name,
                'phone' => $request->phone,
                'address' => $request->address,
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
        $kontingen->update($request->only('name', 'official_name', 'phone', 'address'));

        $kontingen->user->update($request->only('name', 'username', 'email'));

        return redirect()->route('kontingen.index')
            ->with('success', 'Data kontingen berhasil diperbarui');
    }

    public function destroy(Contingent $kontingen): RedirectResponse
    {
        $kontingen->user()->delete();

        return redirect()->route('kontingen.index')
            ->with('success', 'Kontingen berhasil dihapus');
    }
}
