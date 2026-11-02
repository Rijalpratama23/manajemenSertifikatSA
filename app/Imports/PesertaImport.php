<?php

namespace App\Imports;

use App\Models\Peserta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PesertaImport implements ToCollection, WithHeadingRow
{
    private int $importedCount = 0;

    public function collection(Collection $rows): void
    {
        $participants = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $attributes = [
                'nama' => trim((string) ($row['nama'] ?? '')),
                'email' => trim((string) ($row['email'] ?? '')),
                'asal_instansi' => trim((string) ($row['asal_instansi'] ?? '')) ?: null,
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
                Peserta::query()->create($attributes);
            }
        });

        $this->importedCount = count($participants);
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }
}
