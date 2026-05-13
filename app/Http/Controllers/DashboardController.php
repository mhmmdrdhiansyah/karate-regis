<?php

namespace App\Http\Controllers;

use App\Models\Contingent;
use App\Models\Participant;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return view('dashboard.index', [
                'role' => 'super-admin',
                'totalUsers' => User::count(),
                'totalKontingen' => Contingent::count(),
                'recentUsers' => User::latest()->take(5)->get(),
            ]);
        }

        if ($user->isPanitia()) {
            return view('dashboard.index', [
                'role' => 'panitia',
                'totalKontingen' => Contingent::count(),
            ]);
        }

        return view('dashboard.index', [
            'role' => 'kontingen',
            'user' => $user,
            'contingent' => $user->contingent,
            'totalAthletes' => $user->contingent?->participants()->athletes()->count() ?? 0,
            'totalCoaches' => $user->contingent?->participants()->coaches()->count() ?? 0,
            'totalOfficials' => $user->contingent?->participants()->where('type', 'official')->count() ?? 0,
            'totalVerified' => $user->contingent?->participants()->where('is_verified', true)->count() ?? 0,
        ]);
    }
}
