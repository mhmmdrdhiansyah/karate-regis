<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount(['categories', 'payments'])
            ->orderByDesc('event_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create', [
            'event' => new Event(['status' => EventStatus::Draft]),
        ]);
    }

    public function store(EventRequest $request)
    {
        $event = Event::create($request->validated());

        return redirect()->route('admin.events.show', $event)->with('success', 'Event berhasil dibuat.');
    }

    public function show(Event $event)
    {
        $event->load(['categories.subCategories.registrations.payment']);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(EventRequest $request, Event $event)
    {
        $validated = $request->validated();

        if ($event->isLocked()) {
            unset($validated['event_date'], $validated['coach_fee']);
        }

        unset($validated['status']);

        $event->update($validated);

        return redirect()->route('admin.events.show', $event)->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        if ($event->categories()->exists() || $event->payments()->exists()) {
            return back()->withErrors([
                'delete' => 'Event tidak dapat dihapus karena sudah memiliki kategori atau pembayaran.',
            ]);
        }

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus.');
    }

    public function transition(Request $request, Event $event)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(EventStatus::class)],
        ]);

        $nextStatus = EventStatus::from($validated['status']);

        if (! $event->canTransitionTo($nextStatus)) {
            return back()->withErrors([
                'transition' => 'Transisi status tidak valid.',
            ]);
        }

        $event->update(['status' => $nextStatus]);

        return back()->with('success', 'Status event berhasil diubah.');
    }
}
