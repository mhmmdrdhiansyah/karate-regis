<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Services\EventStatsService;
use App\Services\StandingsService;

class LandingController extends Controller
{
    /**
     * Display the landing page with upcoming events.
     */
    public function index()
    {
        // Get active events: registration_open, registration_closed, ongoing
        $activeEvents = Event::query()
            ->whereIn('status', ['registration_open', 'registration_closed', 'ongoing'])
            ->orderBy('event_date', 'asc')
            ->take(7)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'date' => $event->formatted_date, // Uses accessor: 'd M Y' format
                    'type' => strtoupper($event->statusLabel()),
                    'title' => $event->name,
                    'location' => 'TBD', // Event model doesn't have location field yet
                    'image' => $event->image_url, // Uses accessor
                ];
            });

        // Get completed events
        $completedEvents = Event::query()
            ->where('status', 'completed')
            ->orderBy('event_date', 'desc')
            ->take(7)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'date' => $event->formatted_date,
                    'type' => strtoupper($event->statusLabel()),
                    'title' => $event->name,
                    'location' => 'TBD',
                    'image' => $event->image_url,
                ];
            });

        return view('welcome', compact('activeEvents', 'completedEvents'));
    }

    /**
     * Display the public detail page for a single event.
     */
    public function show(Event $event)
    {
        // Draft events are not announced publicly yet.
        abort_unless($event->status !== EventStatus::Draft, 404);

        $event->load(['categories.subCategories', 'files']);

        $groupedCategories = $event->categories->groupBy(fn ($category) => $category->type->value);

        $stats = app(EventStatsService::class)->forEvent($event);

        return view('event-detail', compact('event', 'groupedCategories', 'stats'));
    }

    /**
     * Check registration status (placeholder for future implementation).
     */
    public function checkStatus(Request $request)
    {
        // TODO: Implement registration status check logic
        // This could search by email or registration ID
        return redirect()->route('login')
            ->with('info', 'Fitur cek status sedang dalam pengembangan. Silakan login terlebih dahulu.');
    }

    /**
     * Display the public standings page for an event.
     * Satu section per tipe resmi (Open, TNI/Polri, Mahasiswa) — hanya
     * tipe yang kategorinya ADA di event; Festival tidak pernah dihitung.
     */
    public function klasemen(Event $event)
    {
        // Jangan tampilkan klasemen untuk event draft
        abort_unless($event->status !== EventStatus::Draft, 404);

        $event->load('categories');

        // Tipe resmi yang klasemennya ditampilkan, urutan section tetap
        $jenisKlasemen = [
            \App\Enums\EventCategoryType::Open,
            \App\Enums\EventCategoryType::TniPolri,
            \App\Enums\EventCategoryType::Mahasiswa,
        ];
        $tipeDiEvent = $event->categories->pluck('type')->unique();

        $standingsPerTipe = [];
        foreach ($jenisKlasemen as $tipe) {
            if (! $tipeDiEvent->contains($tipe)) {
                continue; // event tidak punya kategori tipe ini → tanpa section
            }

            $standingsPerTipe[$tipe->value] = app(StandingsService::class)->forEvent($event, $tipe);
        }

        return view('event-klasemen', compact('event', 'standingsPerTipe'));
    }
}
