<?php

namespace Tests\Feature;

use App\Imports\PesertaImport;
use App\Models\Peserta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PesertaImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_the_fourth_template_column_as_the_school_or_university(): void
    {
        $import = new PesertaImport();
        $import->collection(collect([
            [
                'No',
                'Rijal Pratama',
                'rijal@example.test',
                'Universitas Nusa Putra',
            ],
        ]));

        $this->assertSame(1, $import->importedCount());
        $this->assertDatabaseHas('peserta', [
            'nama' => 'Rijal Pratama',
            'email' => 'rijal@example.test',
            'asal_instansi' => 'Universitas Nusa Putra',
        ]);
    }

    public function test_reimport_updates_existing_participant_instead_of_adding_a_duplicate(): void
    {
        Peserta::query()->create([
            'nama' => 'Rijal Pratama',
            'email' => 'rijal@example.test',
            'asal_instansi' => null,
        ]);

        $import = new PesertaImport();
        $import->collection(collect([
            ['1', 'Rijal Pratama', 'rijal@example.test', 'Universitas Nusa Putra'],
        ]));

        $this->assertDatabaseCount('peserta', 1);
        $this->assertDatabaseHas('peserta', [
            'nama' => 'Rijal Pratama',
            'email' => 'rijal@example.test',
            'asal_instansi' => 'Universitas Nusa Putra',
        ]);
    }

    public function test_it_imports_the_institution_from_an_excel_workbook(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Peserta');
        $sheet->fromArray([
            ['No', 'Nama', 'Email', 'Asal Sekolah/Universitas'],
            [1, 'Rijal Pratama', 'rijal@example.test', 'Universitas Nusa Putra'],
        ], null, 'A1');

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Petunjuk');
        $instructions->fromArray([
            ['PETUNJUK PENGISIAN'],
            ['Isi data peserta di sheet Data Peserta.'],
        ], null, 'A1');

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'peserta-import-'.uniqid('', true).'.xlsx';

        try {
            (new Xlsx($spreadsheet))->save($path);

            $workbookRows = Excel::toArray(new PesertaImport(), $path);
            $this->assertSame('Universitas Nusa Putra', $workbookRows[0][0][3] ?? null);

            $import = new PesertaImport();
            Excel::import($import, $path);

            $this->assertDatabaseHas('peserta', [
                'nama' => 'Rijal Pratama',
                'email' => 'rijal@example.test',
                'asal_instansi' => 'Universitas Nusa Putra',
            ]);
            $this->assertSame(1, $import->importedCount());
        } finally {
            $spreadsheet->disconnectWorksheets();
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_it_imports_the_email_free_participant_template_by_column_headers(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Peserta');
        $sheet->fromArray([
            ['No', 'Nama', 'Asal Sekolah/Universitas', 'NIS', 'Tempat Lahir', 'Tanggal Lahir'],
            [1, 'Siswa Kursus', 'SMA Contoh', 202600001, 'Sukabumi', '2009-03-21'],
        ], null, 'A1');

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'peserta-import-no-email-'.uniqid('', true).'.xlsx';

        try {
            (new Xlsx($spreadsheet))->save($path);

            $import = new PesertaImport();
            Excel::import($import, $path);

            $this->assertSame(1, $import->importedCount());
            $this->assertDatabaseHas('peserta', [
                'nama' => 'Siswa Kursus',
                'email' => null,
                'asal_instansi' => 'SMA Contoh',
                'nis' => 202600001,
                'tempat_lahir' => 'Sukabumi',
            ]);
            $this->assertSame(
                '2009-03-21',
                Peserta::query()->where('nis', 202600001)->firstOrFail()->tanggal_lahir->toDateString(),
            );
        } finally {
            $spreadsheet->disconnectWorksheets();
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_it_normalizes_excel_decimal_format_nis_to_an_integer(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Peserta');
        $sheet->fromArray([
            ['No', 'Nama', 'Asal Sekolah/Universitas', 'NIS', 'Tempat Lahir', 'Tanggal Lahir'],
            [1, 'Siswa Kursus', 'SMA Contoh', '200384787345.0', 'Sukabumi', '2009-03-21'],
        ], null, 'A1');

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'peserta-import-decimal-nis-'.uniqid('', true).'.xlsx';

        try {
            (new Xlsx($spreadsheet))->save($path);

            $import = new PesertaImport();
            Excel::import($import, $path);

            $this->assertSame(1, $import->importedCount());
            $this->assertDatabaseHas('peserta', [
                'nama' => 'Siswa Kursus',
                'nis' => 200384787345,
            ]);
        } finally {
            $spreadsheet->disconnectWorksheets();
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_it_rejects_fractional_nis_values(): void
    {
        $this->expectException(ValidationException::class);

        (new PesertaImport())->collection(collect([
            ['No', 'Nama', 'Email', 'Asal Sekolah/Universitas', '202600001.5'],
        ]));
    }

    public function test_it_rejects_duplicate_nis_values_without_overwriting_an_existing_participant(): void
    {
        Peserta::query()->create([
            'nis' => 200384787345,
            'nama' => 'Peserta Sebelumnya',
            'email' => null,
        ]);

        try {
            (new PesertaImport())->collection(collect([
                ['1', 'Peserta Satu', '', 'Sekolah Contoh', '0200384787345'],
                ['2', 'Peserta Dua', '', 'Sekolah Contoh', '0200384787345'],
            ]));

            $this->fail('Impor seharusnya menolak NIS yang berulang.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString(
                'baris 2, 3',
                implode(' ', $exception->errors()['import']),
            );
        }

        $this->assertDatabaseCount('peserta', 1);
        $this->assertDatabaseHas('peserta', [
            'nis' => 200384787345,
            'nama' => 'Peserta Sebelumnya',
        ]);
    }

    public function test_it_treats_a_dash_as_an_empty_optional_nis(): void
    {
        $import = new PesertaImport();
        $import->collection(collect([
            ['No', 'Siswa PKL', 'pkl@example.test', 'Sekolah Contoh', '—'],
        ]));

        $this->assertSame(1, $import->importedCount());
        $this->assertDatabaseHas('peserta', [
            'nama' => 'Siswa PKL',
            'nis' => null,
        ]);
    }
}
