<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JenisSertifikat;
use App\Models\Peserta;
use App\Models\Sertifikat;
use App\Models\TemplateSertifikat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class CertificateArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_filter_and_view_certificate_archive(): void
    {
        $admin = $this->createAdmin();
        $certificate = $this->createCertificate($admin);

        $this->actingAs($admin)
            ->get(route('certificate-archive.index', [
                'search' => 'MAGANG/2026/0001',
                'tanggal' => '2026',
                'status' => Sertifikat::STATUS_AKTIF,
                'jenis' => $certificate->id_jenis,
            ]))
            ->assertOk()
            ->assertSee($certificate->nomor_sertifikat)
            ->assertSee('Arsip Sertifikat')
            ->assertSee('Filter arsip Magang')
            ->assertSee('Unduh semua hasil (ZIP)')
            ->assertViewHas('totalCertificates', 1)
            ->assertViewHas('activeCertificates', 1);

        $this->get(route('certificate-archive.show', $certificate))
            ->assertOk()
            ->assertSee($certificate->nomor_sertifikat)
            ->assertSee($certificate->peserta->email);
    }

    public function test_archive_groups_certificates_into_type_folders_and_scopes_the_selected_folder(): void
    {
        $admin = $this->createAdmin();
        $magangCertificate = $this->createCertificate($admin);
        $kursusCertificate = $this->createCertificate($admin, 'KURSUS', 'Kursus', 'KURSUS/2026/0001');

        $this->actingAs($admin)
            ->get(route('certificate-archive.index'))
            ->assertOk()
            ->assertSee('Folder jenis sertifikat')
            ->assertSee('Magang')
            ->assertSee('Kursus')
            ->assertSee('2 jenis sertifikat tersedia');

        $this->get(route('certificate-archive.index', ['jenis' => $magangCertificate->id_jenis]))
            ->assertOk()
            ->assertSee('Filter arsip Magang')
            ->assertSee($magangCertificate->nomor_sertifikat)
            ->assertDontSee($kursusCertificate->nomor_sertifikat)
            ->assertViewHas('totalCertificates', 1);
    }

    public function test_admin_can_cancel_certificate_and_open_its_reissue_form(): void
    {
        $admin = $this->createAdmin();
        $certificate = $this->createCertificate($admin);

        $this->actingAs($admin)
            ->post(route('certificate-archive.cancel', $certificate))
            ->assertRedirect(route('certificate-archive.index'));

        $this->assertDatabaseHas('sertifikat', [
            'id_sertifikat' => $certificate->id_sertifikat,
            'status' => Sertifikat::STATUS_DIBATALKAN,
            'alasan_batal' => 'Dibatalkan oleh admin melalui arsip sertifikat.',
        ]);

        $this->get(route('certificate-issues.create', ['ulangi' => $certificate->id_sertifikat]))
            ->assertOk()
            ->assertSee('Penerbitan ulang dari sertifikat')
            ->assertSee($certificate->nomor_sertifikat)
            ->assertSee($certificate->peserta->nama);
    }

    public function test_admin_can_download_all_matching_archive_certificates_as_a_zip(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('The PHP Zip extension is required.');
        }

        Storage::fake('local');
        $certificate = $this->createCertificate();
        $certificate->update([
            'berkas_pdf' => 'certificates/test-certificate.pdf',
            'pdf_status' => Sertifikat::PDF_STATUS_COMPLETED,
        ]);
        Storage::disk('local')->put('certificates/test-certificate.pdf', '%PDF test');
        $admin = Admin::query()->where('username', 'admin-archive-test')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('certificate-archive.download', [
                'tanggal' => '2026',
                'jenis' => $certificate->id_jenis,
                'status' => Sertifikat::STATUS_AKTIF,
            ]));

        $response->assertOk()->assertDownload('arsip-sertifikat.zip');
        $archivePath = $response->baseResponse->getFile()->getPathname();

        try {
            $archive = new ZipArchive;
            self::assertTrue($archive->open($archivePath));
            self::assertSame('MAGANG-2026-0001.pdf', $archive->getNameIndex(0));
            $archive->close();
        } finally {
            if (is_file($archivePath)) {
                unlink($archivePath);
            }
        }
    }

    private function createAdmin(): Admin
    {
        return Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-archive-test',
            'password_hash' => 'password',
        ]);
    }

    private function createCertificate(
        ?Admin $admin = null,
        string $typeCode = 'MAGANG',
        string $typeName = 'Magang',
        string $certificateNumber = 'MAGANG/2026/0001',
    ): Sertifikat
    {
        $participant = Peserta::query()->create([
            'nama' => "Doni Prasetyo {$typeCode}",
            'email' => strtolower($typeCode).'@example.test',
            'asal_instansi' => 'SMKN 1 Bandung',
        ]);
        $type = JenisSertifikat::query()->create([
            'kode' => $typeCode,
            'nama_jenis' => $typeName,
            'aktif' => true,
        ]);
        $template = TemplateSertifikat::query()->create([
            'id_jenis' => $type->id_jenis,
            'nama_template' => 'Template Magang',
            'berkas_desain' => 'certificate-templates/background.png',
            'aktif' => true,
        ]);
        $admin ??= $this->createAdmin();

        return Sertifikat::query()->create([
            'nomor_sertifikat' => $certificateNumber,
            'token_publik' => hash('sha256', $typeCode),
            'kode_verifikasi' => strtoupper(substr(hash('sha256', $typeCode), 0, 12)),
            'id_peserta' => $participant->id_peserta,
            'id_jenis' => $type->id_jenis,
            'id_admin' => $admin->id_admin,
            'id_template' => $template->id_template,
            'periode_mulai' => '2026-09-01',
            'periode_selesai' => '2026-11-30',
            'tanggal_terbit' => '2026-10-28',
            'status' => Sertifikat::STATUS_AKTIF,
        ]);
    }
}
