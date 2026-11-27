<?php

namespace Tests\Feature;

use App\Imports\PesertaImport;
use App\Models\Admin;
use App\Models\JenisSertifikat;
use App\Models\Peserta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;
use ZipArchive;

class PesertaTemplateDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_the_participant_import_template_from_the_participant_page(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-template-test',
            'password_hash' => 'password',
        ]);

        $this->actingAs($admin)
            ->get(route('participants.index'))
            ->assertOk()
            ->assertSee(route('participants.template.download'));

        $this->get(route('participants.template.download'))
            ->assertOk()
            ->assertDownload('template_data_peserta.xlsx');

        $archive = new ZipArchive();
        $this->assertTrue($archive->open(resource_path('templates/template_data_peserta.xlsx')) === true);
        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML($archive->getFromName('xl/worksheets/sheet1.xml')));
        $instructionsXml = $archive->getFromName('xl/worksheets/sheet2.xml');
        $archive->close();
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('sheet', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $headerCells = $xpath->query('/sheet:worksheet/sheet:sheetData/sheet:row[@r="1"]/sheet:c/sheet:is/sheet:t');
        $this->assertSame([
            'No',
            'Nama',
            'Asal Sekolah/Universitas',
            'NIS',
            'Tempat Lahir',
            'Tanggal Lahir',
        ], array_map(fn ($cell) => $cell->textContent, iterator_to_array($headerCells)));
        $institutionEmailValidations = $xpath->query(
            '/sheet:worksheet/sheet:dataValidations/sheet:dataValidation[@sqref="C2:C500"]',
        );
        $this->assertSame(0, $institutionEmailValidations->length);
        $this->assertStringNotContainsString('Email', $instructionsXml);

        $import = new PesertaImport();
        Excel::import($import, resource_path('templates/template_data_peserta.xlsx'));
        $this->assertSame(0, $import->importedCount());
    }

    public function test_participant_page_keeps_selected_type_in_template_link_and_import_redirect(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-template-type-test',
            'password_hash' => 'password',
        ]);
        $type = JenisSertifikat::query()->create([
            'kode' => 'MAGANG',
            'nama_jenis' => 'Magang',
            'aktif' => true,
        ]);
        Peserta::query()->create([
            'nis' => 200384787345,
            'nama' => 'Peserta Magang',
            'email' => null,
            'asal_instansi' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('participants.index', ['jenis' => $type->id_jenis]))
            ->assertOk()
            ->assertSee('name="jenis" value="'.$type->id_jenis.'"', false)
            ->assertSee(route('participants.template.download', ['jenis' => $type->id_jenis]), false);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Peserta');
        $sheet->fromArray([
            ['No', 'Nama', 'Asal Sekolah/Universitas', 'NIS', 'Tempat Lahir', 'Tanggal Lahir'],
            [1, 'Peserta Magang', 'Perusahaan Contoh', 200384787345, 'Bandung', '2005-04-12'],
            [2, 'Peserta Magang Lain', 'Perusahaan Contoh', 200384788999, 'Bandung', '2005-05-13'],
        ], null, 'A1');

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'peserta-import-type-'.uniqid('', true).'.xlsx';

        try {
            (new Xlsx($spreadsheet))->save($path);
            $file = new UploadedFile(
                $path,
                'peserta.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true,
            );

            $this->post(route('participants.import'), [
                'jenis' => $type->id_jenis,
                'file' => $file,
            ])->assertRedirect(route('participants.index', ['jenis' => $type->id_jenis]));

            $this->assertDatabaseCount('peserta', 2);
            $participants = Peserta::query()->orderBy('id_peserta')->get();
            $this->assertCount(2, $participants);
            $this->assertDatabaseHas('peserta_jenis_sertifikat', [
                'id_peserta' => $participants[0]->id_peserta,
                'id_jenis' => $type->id_jenis,
            ]);
            $this->assertDatabaseHas('peserta_jenis_sertifikat', [
                'id_peserta' => $participants[1]->id_peserta,
                'id_jenis' => $type->id_jenis,
            ]);

            $this->get(route('participants.index', ['jenis' => $type->id_jenis]))
                ->assertOk()
                ->assertSee('Peserta Magang')
                ->assertSee('Peserta Magang Lain')
                ->assertSee('200384787345')
                ->assertSee('200384788999')
                ->assertViewHas('participants', fn ($participants) => $participants->total() === 2);
        } finally {
            $spreadsheet->disconnectWorksheets();
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
