<?php

namespace App\Services;

use App\Enums\CertificateScope;
use App\Enums\MedalType;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Support\Collection;

class CertificateService
{
    /**
     * Daftar sertifikat seorang peserta. Satu entri per registration sah (per kategori).
     *
     * @return Collection<int, array{
     *     registration: Registration,
     *     event: Event,
     *     category: string,
     *     status: string,
     *     scope: \App\Enums\CertificateScope,
     * }>
     */
    public function forParticipant(Participant $participant): Collection
    {
        return $participant->registrations()
            ->where('status_berkas', 'verified')
            ->whereNotNull('sub_category_id')           // coach/official tidak dapat
            ->whereHas('payment', fn ($q) => $q->where('status', 'verified'))
            ->with(['subCategory.eventCategory.event', 'result', 'payment'])
            ->get()
            ->filter(fn (Registration $r) => $r->subCategory?->eventCategory?->event !== null)
            ->map(fn (Registration $r) => [
                'registration' => $r,
                'event'        => $r->subCategory->eventCategory->event,
                'category'     => $r->subCategory->eventCategory->class_name . ' — ' . $r->subCategory->name,
                'status'       => $this->statusText($r),
                'scope'        => $this->scopeFor($r),
            ])
            ->values();
    }

    public function statusText(Registration $r): string
    {
        $result = $r->result;
        if ($rank = $result?->rank_name) {
            // Hindari "JUARA JUARA 1" jika rank_name sudah mengandung kata "juara"
            return str_contains(strtolower(trim($rank)), 'juara')
                ? strtoupper(trim($rank))
                : 'JUARA ' . strtoupper(trim($rank));
        }
        if ($result) {
            return 'JUARA ' . match ($result->medal_type) {
                MedalType::Gold => '1', MedalType::Silver => '2', MedalType::Bronze => '3',
            };
        }
        return 'PESERTA';
    }

    public function scopeFor(Registration $r): \App\Enums\CertificateScope
    {
        $result = $r->result;
        if (! $result) {
            return \App\Enums\CertificateScope::Participant;
        }
        $rank = strtolower(trim($result->rank_name ?? ''));
        if ($rank !== '' && ! str_contains($rank, 'juara 1') && ! str_contains($rank, 'juara 2') && ! str_contains($rank, 'juara 3')) {
            return \App\Enums\CertificateScope::ChampionOther;   // "Favorit", "Harapan", dll.
        }
        return match ($result->medal_type) {
            MedalType::Gold => \App\Enums\CertificateScope::ChampionGold,
            MedalType::Silver => \App\Enums\CertificateScope::ChampionSilver,
            MedalType::Bronze => \App\Enums\CertificateScope::ChampionBronze,
        };
    }

    /**
     * Pilih template: scope spesifik event → fallback event → fallback global → null.
     */
    public function resolveTemplate(Event $event, \App\Enums\CertificateScope $scope): ?CertificateTemplate
    {
        $pick = fn (\App\Enums\CertificateScope $s, ?int $eventId) => CertificateTemplate::query()
            ->where('is_active', true)
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId), fn ($q) => $q->whereNull('event_id'))
            ->where('scope', $s)
            ->orderByDesc('id')
            ->first();

        return $pick($scope, $event->id)
            ?? $pick(\App\Enums\CertificateScope::Fallback, $event->id)
            ?? $pick(\App\Enums\CertificateScope::Fallback, null);
    }
}
