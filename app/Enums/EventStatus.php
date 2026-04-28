<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case RegistrationOpen = 'registration_open';
    case RegistrationClosed = 'registration_closed';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
}
