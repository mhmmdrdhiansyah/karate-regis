<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventStatsService
{
    /**
     * Statistik registrasi per event: total entri + breakdown per type kategori
     * (dinamis: Open/Festival/Veteran/...), dengan Open dipecah per discipline
     * (Kata/Kumite/Lainnya) dan Beregu dihitung per peserta + jumlah tim.
     */
    public function forEvent(Event $event): array
    {
        $rows = DB::select("
            SELECT ec.type AS category_type,
                   sc.category_type AS sub_type,
                   sc.discipline,
                   COUNT(reg.id) AS entries,
                   COUNT(DISTINCT reg.team_group_id) AS teams
            FROM registrations reg
            JOIN sub_categories sc   ON sc.id = reg.sub_category_id
            JOIN event_categories ec ON ec.id = sc.event_category_id
            WHERE ec.event_id = :event_id
              AND reg.deleted_at IS NULL
              AND reg.sub_category_id IS NOT NULL
            GROUP BY ec.type, sc.category_type, sc.discipline
        ", ['event_id' => $event->id]);

        $total = 0;
        $byType = [];

        foreach ($rows as $row) {
            $entries = (int) $row->entries;
            $teams = (int) $row->teams;
            $total += $entries;

            $type = $row->category_type;
            $byType[$type] ??= ['total' => 0, 'splits' => []];

            if ($type === 'Open') {
                $key = $row->sub_type === 'beregu'
                    ? 'Beregu'
                    : ($row->discipline === 'kata' ? 'Kata' : ($row->discipline === 'kumite' ? 'Kumite' : 'Lainnya'));

                $byType[$type]['splits'][$key] ??= ['entries' => 0, 'teams' => 0];
                $byType[$type]['splits'][$key]['entries'] += $entries;
                $byType[$type]['splits'][$key]['teams'] += $teams;
            }

            $byType[$type]['total'] += $entries;
        }

        return ['total' => $total, 'by_type' => $byType];
    }
}
