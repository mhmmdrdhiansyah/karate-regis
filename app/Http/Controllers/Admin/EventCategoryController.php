<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventCategoryRequest;
use App\Models\Event;
use App\Models\EventCategory;

class EventCategoryController extends Controller
{
    public function store(EventCategoryRequest $request, Event $event)
    {
        $event->categories()->create($request->validated());

        return redirect()->route('admin.events.show', $event)->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(EventCategory $eventCategory)
    {
        $eventCategory->load('event');

        return view('admin.event-categories.edit', compact('eventCategory'));
    }

    public function update(EventCategoryRequest $request, EventCategory $eventCategory)
    {
        $eventCategory->update($request->validated());

        return redirect()->route('admin.events.show', $eventCategory->event)->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(EventCategory $eventCategory)
    {
        $event = $eventCategory->event;

        if (! $eventCategory->canDelete()) {
            return back()->withErrors([
                'delete' => 'Kategori tidak dapat dihapus karena sudah ada registrasi aktif.',
            ]);
        }

        $eventCategory->delete();

        return redirect()->route('admin.events.show', $event)->with('success', 'Kategori berhasil dihapus.');
    }

    public function show(EventCategory $eventCategory)
    {
        $eventCategory->load(['event', 'subCategories.registrations.payment']);

        return view('admin.event-categories.show', compact('eventCategory'));
    }
}
