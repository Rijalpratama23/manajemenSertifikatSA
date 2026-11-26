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
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PesertaImport implements ToCollection, WithEvents, WithStartRow
{
    private int $importedCount = 0;
    private bool $shouldImportSheet = true;
    private array $columnIndexes = [
        'nama' => 1,
        'email' => 2,
        'asal_instansi' => 3,
        'nis' => 4,
        'tempat_lahir' => 5,
        'tanggal_lahir' => 6,
    ];

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
                if (! $this->shouldImportSheet) {
                    return;
                }

                $header = $worksheet->rangeToArray('A1:Z1', null, true, false, false)[0] ?? [];
                $headerIndexes = [];
                foreach ($header as $index => $value) {
                    $normalized = preg_replace('/[^a-z0-9]/', '', mb_strtolower(trim((string) $value)));
                    if ($normalized !== '') {
                        $headerIndexes[$normalized] = $index;
                    }
                }

                foreach ([
                    'nama' => ['nama', 'namapeserta'],
                    'email' => ['email'],
                    'asal_instansi' => ['asalsekolahuniversitas', 'asalsekolah', 'instansi'],
                    'nis' => ['nis'],
                    'tempat_lahir' => ['tempatlahir'],
                    'tanggal_lahir' => ['tanggallahir'],
                ] as $field => $headers) {
                    foreach ($headers as $normalizedHeader) {
                        if (array_key_exists($normalizedHeader, $headerIndexes)) {
                            $this->columnIndexes[$field] = $headerIndexes[$normalizedHeader];
                            break;
                        }
                    }
                    if (! isset($this->columnIndexes[$field]) || ! array_intersect($headers, array_keys($headerIndexes))) {
                        $this->columnIndexes[$field] = null;
                    }
                }
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
                'nama' => $this->valueFromRow($row, 'nama'),
                'email' => $this->valueFromRow($row, 'email') ?: null,
                'asal_instansi' => $this->valueFromRow($row, 'asal_instansi') ?: null,
                'nis' => $this->valueFromRow($row, 'nis') ?: null,
                'tempat_lahir' => $this->valueFromRow($row, 'tempat_lahir') ?: null,
            ];
            $birthDateIndex = $this->columnIndexes['tanggal_lahir'];
            $birthDate = $birthDateIndex === null ? null : ($row[$birthDateIndex] ?? null);
            if ($birthDate instanceof \DateTimeInterface) {
                $attributes['tanggal_lahir'] = $birthDate->format('Y-m-d');
            } elseif (is_numeric($birthDate) && $birthDate !== '') {
                $attributes['tanggal_lahir'] = Date::excelToDateTimeObject((float) $birthDate)->format('Y-m-d');
            } elseif (is_string($birthDate) && trim($birthDate) !== '') {
                $attributes['tanggal_lahir'] = trim($birthDate);
            } else {
                $attributes['tanggal_lahir'] = null;
            }

            if (
                $attributes['nama'] === ''
                && $attributes['email'] === null
                && $attributes['asal_instansi'] === null
                && $attributes['nis'] === null
                && $attributes['tempat_lahir'] === null
                && $attributes['tanggal_lahir'] === null
            ) {
                continue;
            }

            $validator = Validator::make($attributes, [
                'nama' => ['required', 'string', 'max:150'],
                'email' => ['nullable', 'string', 'email', 'max:150'],
                'asal_instansi' => ['nullable', 'string', 'max:150'],
                'nis' => ['nullable', 'integer', 'min:1'],
                'tempat_lahir' => ['nullable', 'string', 'max:100'],
                'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors['Baris ' . ($index + 2)][] = $message;
                }

                continue;
            }

            $participants[] = array_filter(
                $attributes,
                fn ($value, $key) => ! in_array($key, ['nis', 'tempat_lahir', 'tanggal_lahir'], true)
                    || $value !== null,
                ARRAY_FILTER_USE_BOTH,
            );
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($participants) {
            foreach ($participants as $attributes) {
                $participant = ! empty($attributes['nis'])
                    ? Peserta::query()->where('nis', $attributes['nis'])->first()
                    : null;
                $participant ??= ! empty($attributes['email'])
                    ? Peserta::query()->where('email', $attributes['email'])->first()
                    : null;

                if ($participant) {
                    $participant->update($attributes);
                } else {
                    Peserta::query()->create($attributes);
                }
            }
        });

        $this->importedCount = count($participants);
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }

    private function valueFromRow(Collection|array $row, string $field): string
    {
        $columnIndex = $this->columnIndexes[$field] ?? null;
        return $columnIndex === null ? '' : trim((string) ($row[$columnIndex] ?? ''));
    }
}
