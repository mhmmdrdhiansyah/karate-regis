<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Services\StandingsService;

class LandingController extends Controller
{
    /**
     * Display the landing page with upcoming events.
     */
    public function index()
    {
        // Get upcoming events - RegistrationOpen events with date >= today
        $upcomingEvents = Event::query()
            ->where('status', 'registration_open') // Use string value from database
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date', 'asc')
            ->take(3)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'date' => $event->formatted_date, // Uses accessor: 'd M Y' format
                    'type' => 'MIXED', // Default type since Event model doesn't have event_type field
                    'title' => $event->name,
                    'location' => 'TBD', // Event model doesn't have location field yet
                    'image' => $event->image_url, // Uses accessor
                ];
            });

        return view('welcome', compact('upcomingEvents'));
    }

    /**
     * Display the public detail page for a single event.
     */
    public function show(Event $event)
    {
        // Draft events are not announced publicly yet.
        abort_unless($event->status !== EventStatus::Draft, 404);

        $event->load(['categories.subCategories']);

        $groupedCategories = $event->categories->groupBy(fn ($category) => $category->type->value);

        return view('event-detail', compact('event', 'groupedCategories'));
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
     */
    public function klasemen(Event $event)
    {
        // Jangan tampilkan klasemen untuk event draft
        abort_unless($event->status !== EventStatus::Draft, 404);

        $standings = app(StandingsService::class)->forEvent($event);

        return view('event-klasemen', compact('event', 'standings'));
    }
}
