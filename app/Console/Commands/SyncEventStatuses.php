<?php

namespace App\Console\Commands;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Console\Command;

class SyncEventStatuses extends Command
{
    protected $signature = 'events:sync-statuses';

    protected $description = 'Sinkronkan status event berdasarkan batas pendaftaran dan tanggal event';

    public function handle(): int
    {
        $updatedToClosed = 0;
        $updatedToOngoing = 0;
        $today = now()->startOfDay();

        Event::query()
            ->where('status', EventStatus::RegistrationOpen->value)
            ->orderBy('id')
            ->chunkById(100, function ($events) use (&$updatedToClosed, &$updatedToOngoing, $today) {
                foreach ($events as $event) {
                    $shouldCloseRegistration = $event->registration_deadline
                        ? $event->registration_deadline->lte(now())
                        : $event->event_date->lte($today);

                    if ($shouldCloseRegistration) {
                        $event->status = EventStatus::RegistrationClosed;
                        $event->save();
                        $updatedToClosed++;
                    }

                    if ($event->event_date->lte($today)) {
                        $event->status = EventStatus::Ongoing;
                        $event->save();
                        $updatedToOngoing++;
                    }
                }
            });

        Event::query()
            ->where('status', EventStatus::RegistrationClosed->value)
            ->whereDate('event_date', '<=', $today)
            ->orderBy('id')
            ->chunkById(100, function ($events) use (&$updatedToOngoing) {
                foreach ($events as $event) {
                    $event->status = EventStatus::Ongoing;
                    $event->save();
                    $updatedToOngoing++;
                }
            });

        $this->info("Status event disinkronkan. Ditutup: {$updatedToClosed}, dimulai: {$updatedToOngoing}");

        return self::SUCCESS;
    }
}
