<?php

namespace App\Services;

use App\Models\Peserta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CourseParticipantSyncService
{
    public function sync(): int
    {
        if (! Schema::hasTable('kursus')) {
            return 0;
        }

        return DB::transaction(function (): int {
            $courseParticipants = DB::table('kursus')
                ->select(['nis', 'nama'])
                ->whereNotNull('nis')
                ->get()
                ->keyBy(fn ($participant) => (string) $participant->nis)
                ->values();

            if ($courseParticipants->isEmpty()) {
                return 0;
            }

            $nisValues = $courseParticipants->pluck('nis')->all();
            $participantsByNis = Peserta::query()
                ->withCount('sertifikat')
                ->whereIn('nis', $nisValues)
                ->get()
                ->keyBy(fn (Peserta $participant) => (string) $participant->nis);
            $unlinkedParticipantsByName = Peserta::query()
                ->withCount('sertifikat')
                ->whereNull('nis')
                ->get(['id_peserta', 'nama'])
                ->groupBy(fn (Peserta $participant) => $this->normalizeName($participant->nama));
            $courseNameCounts = $courseParticipants->countBy(
                fn ($participant) => $this->normalizeName($participant->nama),
            );
            $createdParticipants = [];
            $syncedCount = 0;

            foreach ($courseParticipants as $courseParticipant) {
                $nis = (string) $courseParticipant->nis;
                if ($participantsByNis->has($nis)) {
                    $participant = $participantsByNis->get($nis);
                    if (
                        $participant->sertifikat_count === 0
                        && $participant->nama !== $courseParticipant->nama
                    ) {
                        $participant->update(['nama' => $courseParticipant->nama]);
                    }

                    continue;
                }

                $nameKey = $this->normalizeName($courseParticipant->nama);
                $nameMatches = $unlinkedParticipantsByName->get($nameKey, collect());

                if ($courseNameCounts->get($nameKey) === 1 && $nameMatches->count() === 1) {
                    $participant = $nameMatches->first();
                    $participant->update([
                        'nis' => $courseParticipant->nis,
                        ...($participant->sertifikat_count === 0 ? ['nama' => $courseParticipant->nama] : []),
                    ]);
                    $participantsByNis->put($nis, $participant);
                    $unlinkedParticipantsByName->forget($nameKey);
                    $syncedCount++;

                    continue;
                }

                $createdParticipants[] = [
                    'nis' => $courseParticipant->nis,
                    'nama' => $courseParticipant->nama,
                    'email' => null,
                    'asal_instansi' => null,
                    'dibuat_pada' => now(),
                    'diubah_pada' => now(),
                ];
                $syncedCount++;
            }

            foreach (array_chunk($createdParticipants, 100) as $participants) {
                DB::table('peserta')->insertOrIgnore($participants);
            }

            return $syncedCount;
        }, attempts: 3);
    }

    private function normalizeName(string $name): string
    {
        return mb_strtolower(trim($name));
    }
}
