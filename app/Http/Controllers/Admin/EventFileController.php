<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:edit events');
    }

    public function store(Request $request, Event $event)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        $path = $request->file('file')->store('events/files', 'public');

        $event->files()->create([
            'name' => $request->name,
            'file_path' => $path,
        ]);

        return back()->with('success', 'File berhasil diunggah.');
    }

    public function destroy(Event $event, EventFile $eventFile)
    {
        if ($eventFile->event_id !== $event->id) {
            abort(404);
        }

        Storage::disk('public')->delete($eventFile->file_path);
        $eventFile->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }
}
