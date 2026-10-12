<?php

namespace App\Services;

use App\Models\Event;
use App\Models\IdCardTemplate;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Support\Collection;

class IdCardService
{
    /**
     * Kartu ID seorang peserta: satu kartu per event yang diikuti via
     * registrasi sah (berkas verified + bayar verified + punya sub-kategori).
     *
     * @return Collection<int, array{participant: Participant, event: Event, categories: string}>
     */
    public function forParticipant(Participant $participant): Collection
    {
        return $participant->registrations()
            ->where('status_berkas', 'verified')
            ->whereNotNull('sub_category_id')
            ->whereHas('payment', fn ($query) => $query->where('status', 'verified'))
            ->with(['subCategory.eventCategory.event'])
            ->get()
            ->filter(fn (Registration $registration) => $registration->subCategory?->eventCategory?->event !== null)
            ->groupBy(fn (Registration $registration) => $registration->subCategory->eventCategory->event->id)
            ->map(fn (Collection $registrations) => [
                'participant' => $participant,
                'event' => $registrations->first()->subCategory->eventCategory->event,
                'categories' => $registrations->map(fn (Registration $registration) => $registration->subCategory->name)->unique()->implode(', '),
            ])
            ->values();
    }

    /**
     * Template ID card untuk event: spesifik event → fallback global → null.
     */
    public function resolveTemplate(Event $event): ?IdCardTemplate
    {
        return IdCardTemplate::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('event_id', $event->id)->orWhereNull('event_id'))
            ->orderByDesc('event_id')
            ->orderByDesc('id')
            ->first();
    }
}
