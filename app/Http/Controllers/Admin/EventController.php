<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventRequest;
use App\Models\Event;
use App\Models\Scopes\ManagedEventScope;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view events')->only(['index', 'show']);
        $this->middleware('permission:create events')->only(['create', 'store']);
        $this->middleware('permission:edit events')->only(['edit', 'update', 'transition']);
        $this->middleware('permission:delete events')->only(['destroy']);
    }

    public function index()
    {
        // Panitia tetap melihat semua event di daftar; tombol "Kelola"
        // dikontrol policy (Tahap 4).
        $events = Event::withoutGlobalScope(ManagedEventScope::class)
            ->select(['id', 'poster', 'name', 'status', 'event_fee'])
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
        $validated = $request->validated();

        if ($request->hasFile('poster')) {
            $validated['poster'] = $this->storePoster($request->file('poster'));
        }

        $event = Event::create($validated);

        // Pembuat event otomatis tercatat sebagai panitia event itu (Tahap 5).
        $event->panitia()->attach(auth()->id());

        return redirect()->route('admin.events.show', $event)->with('success', 'Event berhasil dibuat.');
    }

    public function show(Event $event)
    {
        $event->load(['categories.subCategories.registrations.payment']);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $this->authorize('manage', $event);

        // Daftar panitia untuk penugasan hanya relevan bagi yang boleh assign
        $panitiaUsers = auth()->user()->can('assign event panitia')
            ? User::role('panitia')->orderBy('name')->pluck('name', 'id')
            : collect();

        return view('admin.events.edit', compact('event', 'panitiaUsers'));
    }

    public function update(EventRequest $request, Event $event)
    {
        $this->authorize('manage', $event);

        $validated = $request->validated();

        if ($request->hasFile('poster')) {
            $validated['poster'] = $this->storePoster($request->file('poster'));
            $this->deletePoster($event->poster);
        } else {
            unset($validated['poster']);
        }

        if ($event->isLocked()) {
            unset($validated['event_date'], $validated['coach_fee']);
        }

        unset($validated['status']);

        $event->update($validated);

        return redirect()->route('admin.events.show', $event)->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        $this->authorize('manage', $event);

        if ($event->categories()->exists() || $event->payments()->exists()) {
            return back()->withErrors([
                'delete' => 'Event tidak dapat dihapus karena sudah memiliki kategori atau pembayaran.',
            ]);
        }

        $this->deletePoster($event->poster);

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus.');
    }

    public function transition(Request $request, Event $event)
    {
        $this->authorize('manage', $event);

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

    public function assignPanitia(Request $request, Event $event)
    {
        $this->authorize('manage', $event);
        $this->authorize('assign event panitia');

        $validated = $request->validate([
            'panitia_ids' => ['array'],
            'panitia_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $event->panitia()->sync($validated['panitia_ids'] ?? []);

        return back()->with('success', 'Penugasan panitia berhasil disimpan.');
    }

    private function storePoster(UploadedFile $file): string
    {
        return $file->store('events/posters', 'public');
    }

    private function deletePoster(?string $poster): void
    {
        if ($poster) {
            Storage::disk('public')->delete($poster);
        }
    }
}
