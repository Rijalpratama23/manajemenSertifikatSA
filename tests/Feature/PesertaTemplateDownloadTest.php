<?php

namespace Tests\Feature;

use App\Imports\PesertaImport;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
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
        $this->assertStringNotContainsString('Email', $instructionsXml);

        $import = new PesertaImport();
        Excel::import($import, resource_path('templates/template_data_peserta.xlsx'));
        $this->assertSame(0, $import->importedCount());
    }
}
