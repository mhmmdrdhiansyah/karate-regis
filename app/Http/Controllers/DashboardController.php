<?php

namespace App\Http\Controllers;

use App\Models\Contingent;
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
        ]);
    }
}
