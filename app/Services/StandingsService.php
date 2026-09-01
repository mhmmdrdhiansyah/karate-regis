<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class StandingsService
{
    /**
     * Ambil klasemen medali untuk satu event — HANYA kategori bertipe Open,
     * 1 medali per TIM untuk beregu (COUNT DISTINCT team_group_id), 1 per
     * hasil untuk individu.
     * Medali tipe lain (Festival dst) tetap tersimpan di tabel results dan
     * dipakai untuk status sertifikat, tapi tidak dihitung di klasemen publik.
     *
     * @param  Event  $event
     * @return array  — array of objects, masing-masing berisi:
     *   rank, contingent_id, name, official_name, gold, silver, bronze, total
     */
    public function forEvent(Event $event): array
    {
        $results = DB::select("
            SELECT c.id AS contingent_id,
                   c.name,
                   c.official_name,
                   COUNT(DISTINCT CASE WHEN r.medal_type = 'Gold'   THEN COALESCE(reg.team_group_id, -reg.id) END) AS gold,
                   COUNT(DISTINCT CASE WHEN r.medal_type = 'Silver' THEN COALESCE(reg.team_group_id, -reg.id) END) AS silver,
                   COUNT(DISTINCT CASE WHEN r.medal_type = 'Bronze' THEN COALESCE(reg.team_group_id, -reg.id) END) AS bronze
            FROM results r
            JOIN registrations reg   ON reg.id = r.registration_id
            JOIN sub_categories sc   ON sc.id = reg.sub_category_id
            JOIN event_categories ec ON ec.id = sc.event_category_id
            LEFT JOIN participants p ON p.id = reg.participant_id
            LEFT JOIN team_groups tg ON tg.id = reg.team_group_id
            JOIN contingents c       ON c.id = COALESCE(tg.contingent_id, p.contingent_id)
            WHERE ec.event_id = :event_id
              AND reg.deleted_at IS NULL
              AND reg.sub_category_id IS NOT NULL
              AND ec.type = 'Open'
            GROUP BY c.id, c.name, c.official_name
            ORDER BY gold DESC, silver DESC, bronze DESC, c.name ASC
        ", ['event_id' => $event->id]);

        $standings = [];
        $currentRank = 1;
        $actualRank = 1;
        $previousItem = null;

        foreach ($results as $item) {
            $item->total = $item->gold + $item->silver + $item->bronze;

            if ($previousItem !== null) {
                if ($item->gold == $previousItem->gold && 
                    $item->silver == $previousItem->silver && 
                    $item->bronze == $previousItem->bronze) {
                    $item->rank = $currentRank;
                } else {
                    $currentRank = $actualRank;
                    $item->rank = $currentRank;
                }
            } else {
                $item->rank = $currentRank;
            }

            $previousItem = $item;
            $actualRank++;
            $standings[] = (array) $item; // or object, the prompt asks for array of objects but array cast is safer if they want array, actually says "array of objects"
        }

        // Return array of objects
        $standingsObjects = [];
        foreach ($standings as $stand) {
            $standingsObjects[] = (object) $stand;
        }

        return $standingsObjects;
    }
}
