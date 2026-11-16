<?php

namespace App\Imports;

use App\Models\Peserta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\BeforeSheet;

class PesertaImport implements ToCollection, WithEvents, WithStartRow
{
    private int $importedCount = 0;
    private bool $shouldImportSheet = true;

    public function startRow(): int
    {
        return 2;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event): void {
                $worksheet = $event->getSheet()->getDelegate();
                $this->shouldImportSheet = $worksheet->getTitle() === 'Data Peserta'
                    || $worksheet->getParent()?->getSheetCount() === 1;
            },
        ];
    }

    public function collection(Collection $rows): void
    {
        if (! $this->shouldImportSheet) {
            return;
        }

        $participants = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $attributes = [
                'nama' => trim((string) ($row[1] ?? '')),
                'email' => trim((string) ($row[2] ?? '')),
                'asal_instansi' => trim((string) ($row[3] ?? '')) ?: null,
            ];

            if ($attributes['nama'] === '' && $attributes['email'] === '' && $attributes['asal_instansi'] === null) {
                continue;
            }

            $validator = Validator::make($attributes, [
                'nama' => ['required', 'string', 'max:150'],
                'email' => ['required', 'string', 'email', 'max:150'],
                'asal_instansi' => ['nullable', 'string', 'max:150'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors['Baris ' . ($index + 2)][] = $message;
                }

                continue;
            }

            $participants[] = $attributes;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($participants) {
            foreach ($participants as $attributes) {
                $existingParticipants = Peserta::query()
                    ->where('email', $attributes['email'])
                    ->get();

                if ($existingParticipants->isEmpty()) {
                    Peserta::query()->create($attributes);

                    continue;
                }

                foreach ($existingParticipants as $participant) {
                    $participant->update($attributes);
                }
            }
        });

        $this->importedCount = count($participants);
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }
}
