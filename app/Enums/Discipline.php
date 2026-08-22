<?php

namespace App\Enums;

enum Discipline: string
{
    case Kata = 'kata';
    case Kumite = 'kumite';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Kata => 'Kata',
            self::Kumite => 'Kumite',
            self::Lainnya => 'Lainnya',
        };
    }
}
