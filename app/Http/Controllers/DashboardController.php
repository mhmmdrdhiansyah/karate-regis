<?php

namespace App\Http\Controllers;

use App\Enums\ParticipantType;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $eventCharts = $this->getEventChartData();

            return view('dashboard.index', [
                'role' => 'super-admin',
                'totalUsers' => User::count(),
                'totalKontingen' => Contingent::count(),
                'recentUsers' => User::latest()->take(5)->get(),
                'totalAthletes' => Participant::athletes()->count(),
                'totalCoaches' => Participant::coaches()->count(),
                'totalOfficials' => Participant::where('type', ParticipantType::Official)->count(),
                'totalVerified' => Participant::where('is_verified', true)->count(),
                'totalPending' => Participant::where('is_verified', false)->count(),
                'topKontingen' => Contingent::withCount('participants')
                    ->orderByDesc('participants_count')
                    ->take(10)
                    ->get(),
                'eventCharts' => $eventCharts,
            ]);
        }

        if ($user->isPanitia()) {
            $eventCharts = $this->getEventChartData();

            return view('dashboard.index', [
                'role' => 'panitia',
                'totalKontingen' => Contingent::count(),
                'totalAthletes' => Participant::athletes()->count(),
                'totalCoaches' => Participant::coaches()->count(),
                'totalOfficials' => Participant::where('type', ParticipantType::Official)->count(),
                'totalVerified' => Participant::where('is_verified', true)->count(),
                'totalPending' => Participant::where('is_verified', false)->count(),
                'topKontingen' => Contingent::withCount('participants')
                    ->orderByDesc('participants_count')
                    ->take(10)
                    ->get(),
                'eventCharts' => $eventCharts,
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

    private function getEventChartData(): \Illuminate\Support\Collection
    {
        return Event::with(['categories.subCategories.registrations' => fn($q) => $q->whereHas('payment', fn($p) => $p->where('status', 'verified'))])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($event) => (object) [
                'name' => $event->name,
                'categories' => $event->categories->map(fn($cat) => (object) [
                    'name' => $cat->class_name,
                    'labels' => $cat->subCategories->pluck('name'),
                    'series' => $cat->subCategories->map(fn($sub) => $sub->registrations->count()),
                ]),
            ]);
    }
}
