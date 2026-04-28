<?php

namespace App\Enums;

enum ParticipantType: string
{
    case Athlete = 'athlete';
    case Coach = 'coach';
    case Official = 'official';
}
