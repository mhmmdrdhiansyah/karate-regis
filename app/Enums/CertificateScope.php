<?php

namespace App\Enums;

enum CertificateScope: string
{
    case ChampionGold = 'champion_gold';
    case ChampionSilver = 'champion_silver';
    case ChampionBronze = 'champion_bronze';
    case ChampionOther = 'champion_other';
    case Participant = 'participant';
    case Festival = 'festival';
    case Fallback = 'fallback';
}
