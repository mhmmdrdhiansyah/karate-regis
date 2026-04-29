<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Payment;
use App\Enums\EventStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class RegistrationService
{
    /**
     * Ambil semua event yang bisa didaftarkan.
     */
    public function getOpenEvents(): Collection
    {
        return Event::where('status', EventStatus::RegistrationOpen)
            ->get()
            ->map(function (Event $event) {
                // Tambahan property is_open (boolean)
                $event->is_open = $this->isRegistrationOpen($event);
                return $event;
            });
    }

    /**
     * Cek apakah pendaftaran event masih terbuka (sesuai BR-13).
     */
    public function isRegistrationOpen(Event $event): bool
    {
        if ($event->status !== EventStatus::RegistrationOpen) {
            return false;
        }

        if ($event->registration_deadline === null) {
            return true;
        }

        if (now()->greaterThan($event->registration_deadline)) {
            return false;
        }

        return true;
    }

    /**
     * Return label status pendaftaran untuk ditampilkan di UI.
     */
    public function getRegistrationStatusLabel(Event $event): string
    {
        if ($event->status !== EventStatus::RegistrationOpen) {
            return 'Pendaftaran Belum Dibuka';
        }

        if ($event->registration_deadline !== null) {
            if (now()->greaterThan($event->registration_deadline)) {
                return 'Pendaftaran Ditutup';
            }

            return 'Dibuka hingga ' . $event->registration_deadline->translatedFormat('j F Y');
        }

        return 'Pendaftaran Dibuka';
    }

    /**
     * Ambil daftar event_categories beserta sub_categories-nya, digroup berdasarkan tipe.
     */
    public function getCategoriesForEvent(int $eventId): Collection
    {
        return EventCategory::with('subCategories')
            ->where('event_id', $eventId)
            ->get()
            ->groupBy(function (EventCategory $category) {
                return $category->type->value;
            });
    }

    /**
     * Cek apakah kontingen sudah punya payment aktif untuk event ini (sesuai BR-03).
     */
    public function hasExistingPayment(int $contingentId, int $eventId): bool
    {
        return Payment::where('contingent_id', $contingentId)
            ->where('event_id', $eventId)
            ->where('status', '!=', PaymentStatus::Cancelled)
            ->exists();
    }
}
