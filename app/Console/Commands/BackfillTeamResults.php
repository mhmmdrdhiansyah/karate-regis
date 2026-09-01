<?php

namespace App\Console\Commands;

use App\Models\Result;
use App\Models\TeamGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfill Result juara beregu ke semua anggota tim.
 *
 * Data lama (era input perorangan) menempel juara di SATU registration,
 * padahal juara beregu dimiliki tim. Command ini mereplikasi rank+medali
 * anggota yang sudah ber-Result ke anggota tim yang belum — idempotent:
 * hanya mengisi yang kosong, tidak pernah mengubah/menghapus yang ada.
 */
class BackfillTeamResults extends Command
{
    protected $signature = 'results:backfill-team
        {--dry-run : Tampilkan rencana perubahan tanpa menulis ke DB}';

    protected $description = 'Isi Result juara beregu untuk semua anggota tim (fix data input era perorangan)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $teams = TeamGroup::whereHas('subCategory', fn ($q) => $q->where('category_type', 'beregu'))
            ->with(['registrations' => fn ($q) => $q->whereNull('deleted_at')->with('result')])
            ->get()
            ->filter(fn ($t) => $t->registrations->contains(fn ($r) => $r->result));

        if ($teams->isEmpty()) {
            $this->info('Tidak ada tim juara beregu yang perlu di-backfill.');

            return self::SUCCESS;
        }

        $dibuat = 0;
        $createdIds = [];

        DB::transaction(function () use ($teams, $dryRun, &$dibuat, &$createdIds) {
            foreach ($teams as $team) {
                $contoh = $team->registrations->first(fn ($r) => $r->result)->result;
                $tanpa = $team->registrations->reject(fn ($r) => $r->result);

                if ($tanpa->isEmpty()) {
                    continue; // tim sudah lengkap — idempotent
                }

                $this->line(sprintf(
                    '%s | sub #%d | %s %s → isi %d anggota',
                    $team->team_name,
                    $team->sub_category_id,
                    $contoh->rank_name ?? '-',
                    $contoh->medal_type?->value ?? '-',
                    $tanpa->count(),
                ));

                foreach ($tanpa as $reg) {
                    if ($dryRun) {
                        $dibuat++;
                        continue;
                    }

                    $result = Result::create([
                        'registration_id' => $reg->id,
                        'rank_name' => $contoh->rank_name,
                        'medal_type' => $contoh->medal_type,
                    ]);
                    $createdIds[] = $result->id;
                    $dibuat++;
                }
            }

            if ($dryRun) {
                DB::rollBack(); // pastikan tidak ada yang tertulis
            }
        });

        if ($dryRun) {
            $this->info("[DRY-RUN] Akan membuat {$dibuat} Result. Tidak ada data ditulis.");
        } else {
            $this->info("Selesai: {$dibuat} Result dibuat (id: " . implode(',', $createdIds) . ' — simpan untuk rollback).');
        }

        return self::SUCCESS;
    }
}
