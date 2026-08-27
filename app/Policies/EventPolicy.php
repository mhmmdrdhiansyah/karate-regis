<?php

namespace App\Policies;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function manage(User $user, Event $event): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Event completed → read-only untuk panitia
        if ($event->status === EventStatus::Completed) {
            return false;
        }

        return $user->hasRole('panitia') && $user->managesEvent($event);
    }

    public function view(User $user, Event $event): bool
    {
        return $user->hasRole(['super-admin', 'panitia']);
    }
}
