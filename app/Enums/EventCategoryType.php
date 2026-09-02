<?php

namespace App\Enums;

enum EventCategoryType: string
{
    case Open = 'Open';
    case Festival = 'Festival';
    case TniPolri = 'TNI/Polri';
    case Mahasiswa = 'Mahasiswa';
}
