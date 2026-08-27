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

        if ($user->isSuperAdmin() || $user->can('view users') || $user->can('manage settings')) {
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

        // Check if user is a Kontingen (or has a contingent)
        if ($user->hasRole('kontingen') || $user->contingent || $user->can('edit own kontingen')) {
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

        // Fallback for Panitia or Observer/Viewer roles (shows general charts and totals)
        // Event Scoping: statistik panitia dibatasi ke event yang dipegangnya.
        $managedEvents = $user->managedEvents()->with(['categories.subCategories.registrations.payment'])->get();

        if ($user->hasRole('panitia') && $managedEvents->isEmpty()) {
            return view('dashboard.index', ['role' => 'panitia-empty']);
        }

        $eventIds = $managedEvents->pluck('id');

        $managedPayments = \App\Models\Payment::whereIn('event_id', $eventIds)
            ->where('status', 'pending')
            ->with('registrations')
            ->get();
        $pendingRegistrationIds = $managedPayments->flatMap->registrations->pluck('participant_id')->unique();

        $scopedAthletes = Participant::athletes()
            ->whereHas('registrations.payment', fn ($q) => $q->whereIn('event_id', $eventIds))
            ->count();
        $scopedCoaches = Participant::coaches()
            ->whereHas('registrations.payment', fn ($q) => $q->whereIn('event_id', $eventIds))
            ->count();
        $scopedOfficials = Participant::where('type', ParticipantType::Official)
            ->whereHas('registrations.payment', fn ($q) => $q->whereIn('event_id', $eventIds))
            ->count();
        $scopedVerified = Participant::where('is_verified', true)
            ->whereHas('registrations.payment', fn ($q) => $q->whereIn('event_id', $eventIds))
            ->count();
        $scopedKontingen = Contingent::whereHas('payments', fn ($q) => $q->whereIn('event_id', $eventIds))
            ->count();

        $eventCharts = $managedEvents
            ->filter(fn ($event) => $event->status !== \App\Enums\EventStatus::Completed)
            ->map(fn($event) => (object) [
                'name' => $event->name,
                'categories' => $event->categories->map(fn($cat) => (object) [
                    'name' => $cat->class_name,
                    'labels' => $cat->subCategories->pluck('name'),
                    // fix = berkas verified + pembayaran verified (solid); pending = sisanya (shadow)
                    'seriesFix' => $cat->subCategories->map(fn($sub) => $sub->registrations
                        ->filter(fn($r) => $r->status_berkas === 'verified' && $r->payment?->status === 'verified')->count()),
                    'seriesPending' => $cat->subCategories->map(fn($sub) => $sub->registrations
                        ->reject(fn($r) => $r->status_berkas === 'verified' && $r->payment?->status === 'verified')->count()),
                ]),
            ])
            ->values();

        return view('dashboard.index', [
            'role' => 'panitia',
            'totalKontingen' => $scopedKontingen,
            'totalAthletes' => $scopedAthletes,
            'totalCoaches' => $scopedCoaches,
            'totalOfficials' => $scopedOfficials,
            'totalVerified' => $scopedVerified,
            'totalPending' => $pendingRegistrationIds->count(),
            'pendingPayments' => $managedPayments->count(),
            'topKontingen' => Contingent::withCount('participants')
                ->whereHas('payments', fn ($q) => $q->whereIn('event_id', $eventIds))
                ->orderByDesc('participants_count')
                ->take(10)
                ->get(),
            'eventCharts' => $eventCharts,
        ]);
    }

    private function getEventChartData(): \Illuminate\Support\Collection
    {
        // Event completed tidak ditampilkan lagi di dashboard (issue user)
        return Event::query()
            ->where('status', '!=', \App\Enums\EventStatus::Completed)
            ->with(['categories.subCategories.registrations.payment'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($event) => (object) [
                'name' => $event->name,
                'categories' => $event->categories->map(fn($cat) => (object) [
                    'name' => $cat->class_name,
                    'labels' => $cat->subCategories->pluck('name'),
                    // fix = berkas verified + pembayaran verified (solid); pending = sisanya (shadow)
                    'seriesFix' => $cat->subCategories->map(fn($sub) => $sub->registrations
                        ->filter(fn($r) => $r->status_berkas === 'verified' && $r->payment?->status === 'verified')->count()),
                    'seriesPending' => $cat->subCategories->map(fn($sub) => $sub->registrations
                        ->reject(fn($r) => $r->status_berkas === 'verified' && $r->payment?->status === 'verified')->count()),
                ]),
            ]);
    }
}
