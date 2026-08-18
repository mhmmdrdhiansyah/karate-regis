<?php

namespace App\Enums;

enum SubCategoryGender: string
{
    case Male = 'M';
    case Female = 'F';
    case Mixed = 'Mixed';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Putra',
            self::Female => 'Putri',
            self::Mixed => 'Campuran',
        };
    }
}
