<?php

namespace Tests\Feature;

use App\Jobs\GenerateCertificatePdf;
use App\Models\Admin;
use App\Models\JenisSertifikat;
use App\Models\Peserta;
use App\Models\Sertifikat;
use App\Models\TemplateSertifikat;
use App\Services\CertificatePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;
use ZipArchive;

class CertificateIssuanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_issue_certificate_and_download_its_private_pdf(): void
    {
        Storage::fake('local');
        Queue::fake();
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-test',
            'password_hash' => 'password',
        ]);
        $participant = Peserta::query()->create([
            'nama' => 'Doni Prasetyo',
            'email' => 'doni@example.test',
            'asal_instansi' => 'SMKN 1 Bandung',
        ]);
        $type = JenisSertifikat::query()->create([
            'kode' => 'MAGANG',
            'nama_jenis' => 'Magang',
            'aktif' => true,
        ]);
        $template = TemplateSertifikat::query()->create([
            'id_jenis' => $type->id_jenis,
            'nama_template' => 'Template Magang',
            'berkas_desain' => 'certificate-templates/background.png',
            'konfigurasi_posisi' => [
                'nama' => ['x' => 37.5, 'y' => 62, 'size' => 28, 'visible' => true],
            ],
            'aktif' => true,
        ]);

        $issuancePage = $this->actingAs($admin)->get(route('certificate-issues.create'));
        $issuancePage
            ->assertOk()
            ->assertSee('Terbitkan Sertifikat')
            ->assertSee('Doni Prasetyo')
            ->assertSee('Template Magang')
            ->assertSee('data-template-positions=', false)
            ->assertSee('37.5');

        $response = $this->actingAs($admin)->post(route('certificate-issues.store'), [
            'id_peserta' => $participant->id_peserta,
            'id_jenis' => $type->id_jenis,
            'id_template' => $template->id_template,
            'tanggal_terbit' => '2026-10-28',
            'periode_mulai' => '2026-09-01',
            'periode_selesai' => '2026-11-30',
            'data_pendukung' => [
                'instansi' => 'PT Sukarobot',
                'posisi' => 'Software Developer',
                'durasi' => '3 bulan',
            ],
            'deskripsi' => 'Telah menyelesaikan program magang dengan baik.',
        ]);

        $response->assertRedirect(route('certificate-issues.create', ['batch' => 1]));
        $certificate = Sertifikat::query()->firstOrFail();
        $this->assertSame('MAGANG/2026/0001', $certificate->nomor_sertifikat);
        $this->assertSame($admin->id_admin, $certificate->id_admin);
        $this->assertSame($template->id_template, $certificate->id_template);
        $this->assertSame('Software Developer', $certificate->data_pendukung['posisi']);
        $this->assertSame('3 bulan', $certificate->durasi);
        $this->assertSame(Sertifikat::PDF_STATUS_PENDING, $certificate->pdf_status);
        $this->assertNull($certificate->berkas_pdf);
        Queue::assertPushed(GenerateCertificatePdf::class, 1);
        $this->mock(CertificatePdfService::class, function (MockInterface $mock) {
            $mock->shouldReceive('render')->once()->andReturn('%PDF-test');
        });
        Queue::pushed(GenerateCertificatePdf::class)->first()
            ->handle(app(CertificatePdfService::class));
        $certificate->refresh();
        $this->assertSame(Sertifikat::PDF_STATUS_COMPLETED, $certificate->pdf_status);
        Storage::disk('local')->assertExists($certificate->berkas_pdf);

        $this->actingAs($admin)
            ->get(route('certificate-issues.create', ['batch' => 1]))
            ->assertOk()
            ->assertSee('Sertifikat berhasil diterbitkan')
            ->assertSee($certificate->nomor_sertifikat)
            ->assertSee('Hasil PDF');

        $this->get(route('certificate-issues.preview', $certificate))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename="MAGANG-2026-0001.pdf"');

        $this->actingAs($admin)
            ->get(route('certificate-issues.download', $certificate))
            ->assertOk();

        $this->get(route('certificates.verify', $certificate->token_publik))
            ->assertOk()
            ->assertSee($certificate->nomor_sertifikat)
            ->assertSee($participant->nama)
            ->assertSee('Sertifikat ditemukan')
            ->assertSee(route('certificates.verify.pdf', $certificate->token_publik))
            ->assertSee(route('certificates.verify.download', $certificate->token_publik))
            ->assertDontSee($participant->email);

        $this->get(route('certificates.verify.pdf', $certificate->token_publik))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename="MAGANG-2026-0001.pdf"');

        $this->get(route('certificates.verify.download', $certificate->token_publik))
            ->assertOk()
            ->assertDownload('MAGANG-2026-0001.pdf');
    }

    public function test_issue_page_requires_an_authenticated_admin(): void
    {
        $this->get(route('certificate-issues.create'))->assertRedirect(route('login'));
    }

    public function test_pdf_generation_completes_immediately_when_certificate_queue_uses_sync(): void
    {
        Storage::fake('local');
        config(['queue.connections.certificate_pdfs.driver' => 'sync']);

        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-sync-pdf-test',
            'password_hash' => 'password',
        ]);
        $participant = Peserta::query()->create([
            'nama' => 'Doni Prasetyo',
            'email' => 'doni@example.test',
        ]);
        $type = JenisSertifikat::query()->create([
            'kode' => 'MAGANG',
            'nama_jenis' => 'Magang',
            'aktif' => true,
        ]);
        $template = TemplateSertifikat::query()->create([
            'id_jenis' => $type->id_jenis,
            'nama_template' => 'Template Magang',
            'berkas_desain' => 'certificate-templates/background.png',
            'aktif' => true,
        ]);
        $this->mock(CertificatePdfService::class, function (MockInterface $mock) {
            $mock->shouldReceive('render')->once()->andReturn('%PDF-test');
        });

        $this->actingAs($admin)->post(route('certificate-issues.store'), [
            'id_peserta' => $participant->id_peserta,
            'id_jenis' => $type->id_jenis,
            'id_template' => $template->id_template,
            'tanggal_terbit' => '2026-10-28',
            'periode_mulai' => '2026-09-01',
            'periode_selesai' => '2026-11-30',
            'data_pendukung' => [
                'instansi' => 'PT Sukarobot',
                'posisi' => 'Software Developer',
                'durasi' => '3 bulan',
            ],
            'deskripsi' => 'Telah menyelesaikan program magang.',
        ])->assertRedirect(route('certificate-issues.create', ['batch' => 1]));

        $certificate = Sertifikat::query()->firstOrFail();
        $this->assertSame(Sertifikat::PDF_STATUS_COMPLETED, $certificate->pdf_status);
        Storage::disk('local')->assertExists($certificate->berkas_pdf);
    }

    public function test_template_visibility_settings_hide_elements_from_the_certificate_output(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-template-visibility-test',
            'password_hash' => 'password',
        ]);
        $participant = Peserta::query()->create([
            'nama' => 'Doni Prasetyo',
            'email' => 'doni@example.test',
        ]);
        $type = JenisSertifikat::query()->create([
            'kode' => 'MAGANG',
            'nama_jenis' => 'Magang',
            'aktif' => true,
        ]);
        $template = TemplateSertifikat::query()->create([
            'id_jenis' => $type->id_jenis,
            'nama_template' => 'Template Magang',
            'berkas_desain' => 'certificate-templates/background.png',
            'konfigurasi_posisi' => [
                'judul' => ['visible' => false],
                'nama' => ['x' => 50.5, 'y' => 44.5, 'size' => 25],
                'qr' => ['visible' => false],
            ],
            'aktif' => true,
        ]);
        $certificate = Sertifikat::query()->create([
            'nomor_sertifikat' => 'MAGANG/2026/0001',
            'token_publik' => str_repeat('c', 64),
            'kode_verifikasi' => 'ABCDEF123456',
            'id_peserta' => $participant->id_peserta,
            'id_jenis' => $type->id_jenis,
            'id_admin' => $admin->id_admin,
            'id_template' => $template->id_template,
            'periode_mulai' => '2026-09-01',
            'periode_selesai' => '2026-11-30',
            'tanggal_terbit' => '2026-10-28',
            'status' => Sertifikat::STATUS_AKTIF,
        ])->load(['peserta', 'jenis', 'template']);

        $html = view('certificates.pdf', [
            'sertifikat' => $certificate,
            'background' => 'data:image/png;base64,',
            'qrCode' => 'data:image/png;base64,',
        ])->render();

        $this->assertStringNotContainsString('SERTIFIKAT</div>', $html);
        $this->assertStringNotContainsString('alt="QR verifikasi"', $html);
        $this->assertStringContainsString(
            'top: 93.45mm; left: 149.99mm; width: 160mm; margin-left:-80mm; margin-top:-0.5em;',
            $html,
        );
        $this->assertStringContainsString($participant->nama, $html);
        $this->assertStringContainsString($certificate->nomor_sertifikat, $html);
    }

    public function test_public_verification_marks_cancelled_certificates_invalid_without_pdf_actions(): void
    {
        $participant = Peserta::query()->create([
            'nama' => 'Doni Prasetyo',
            'email' => 'doni@example.test',
        ]);
        $type = JenisSertifikat::query()->create([
            'kode' => 'MAGANG',
            'nama_jenis' => 'Magang',
            'aktif' => true,
        ]);
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-cancelled-test',
            'password_hash' => 'password',
        ]);
        $template = TemplateSertifikat::query()->create([
            'id_jenis' => $type->id_jenis,
            'nama_template' => 'Template Magang',
            'berkas_desain' => 'certificate-templates/background.png',
            'aktif' => true,
        ]);
        $certificate = Sertifikat::query()->create([
            'nomor_sertifikat' => 'MAGANG/2026/0001',
            'token_publik' => str_repeat('b', 64),
            'kode_verifikasi' => 'ABCDEF654321',
            'id_peserta' => $participant->id_peserta,
            'id_jenis' => $type->id_jenis,
            'id_admin' => $admin->id_admin,
            'id_template' => $template->id_template,
            'periode_mulai' => '2026-09-01',
            'periode_selesai' => '2026-11-30',
            'tanggal_terbit' => '2026-10-28',
            'status' => Sertifikat::STATUS_DIBATALKAN,
        ]);

        $this->get(route('certificates.verify', $certificate->token_publik))
            ->assertOk()
            ->assertSee('Sertifikat tidak berlaku')
            ->assertDontSee('Unduh PDF');

        $this->get(route('certificates.verify.download', $certificate->token_publik))
            ->assertNotFound();
    }

    public function test_invalid_period_does_not_consume_a_certificate_number(): void
    {
        Storage::fake('local');
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-test',
            'password_hash' => 'password',
        ]);
        $participant = Peserta::query()->create([
            'nama' => 'Doni Prasetyo',
            'email' => 'doni@example.test',
        ]);
        $type = JenisSertifikat::query()->create([
            'kode' => 'MAGANG',
            'nama_jenis' => 'Magang',
            'aktif' => true,
        ]);
        $template = TemplateSertifikat::query()->create([
            'id_jenis' => $type->id_jenis,
            'nama_template' => 'Template Magang',
            'berkas_desain' => 'certificate-templates/background.png',
            'aktif' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('certificate-issues.store'), [
            'id_peserta' => $participant->id_peserta,
            'id_jenis' => $type->id_jenis,
            'id_template' => $template->id_template,
            'tanggal_terbit' => '2026-10-28',
            'periode_mulai' => '2026-11-30',
            'periode_selesai' => '2026-09-01',
            'data_pendukung' => [
                'instansi' => 'PT Sukarobot',
                'posisi' => 'Software Developer',
                'durasi' => '3 bulan',
            ],
            'deskripsi' => 'Sertifikat ujian kompetensi.',
        ]);

        $response->assertSessionHasErrors('periode_selesai');
        $this->assertDatabaseCount('sertifikat', 0);
        $this->assertDatabaseCount('nomor_urut', 0);
    }

    public function test_admin_can_issue_certificates_to_multiple_selected_participants(): void
    {
        Storage::fake('local');
        Queue::fake();
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-batch-test',
            'password_hash' => 'password',
        ]);
        $participants = collect(['Doni Prasetyo', 'Siti Aminah'])
            ->merge(collect(range(3, 10))->map(fn ($number) => sprintf('Peserta %02d', $number)))
            ->map(fn ($name) => Peserta::query()->create([
                'nama' => $name,
                'email' => str_replace(' ', '.', strtolower($name)).'@example.test',
            ]));
        $type = JenisSertifikat::query()->create([
            'kode' => 'MAGANG',
            'nama_jenis' => 'Magang',
            'aktif' => true,
        ]);
        $template = TemplateSertifikat::query()->create([
            'id_jenis' => $type->id_jenis,
            'nama_template' => 'Template Magang',
            'berkas_desain' => 'certificate-templates/background.png',
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('certificate-issues.create'))
            ->assertOk()
            ->assertSee('name="id_peserta[]"', false)
            ->assertSee('Pilih semua peserta yang tampil')
            ->assertSee('Pilih jenis sertifikat untuk melihat peserta yang dapat dipilih.')
            ->assertSee('Instansi/perusahaan magang');

        $response = $this->actingAs($admin)->post(route('certificate-issues.store'), [
            'id_peserta' => $participants->pluck('id_peserta')->all(),
            'id_jenis' => $type->id_jenis,
            'id_template' => $template->id_template,
            'tanggal_terbit' => '2026-10-28',
            'periode_mulai' => '2026-09-01',
            'periode_selesai' => '2026-11-30',
            'data_pendukung' => [
                'instansi' => 'PT Sukarobot',
                'posisi' => 'Software Developer',
                'durasi' => '3 bulan',
            ],
            'deskripsi' => 'Telah menyelesaikan program magang.',
        ]);

        $response->assertRedirect(route('certificate-issues.create', ['batch' => 1]));
        $this->assertDatabaseCount('sertifikat', 10);
        $certificates = Sertifikat::query()->orderBy('id_sertifikat')->get();
        $this->assertSame('MAGANG/2026/0001', $certificates[0]->nomor_sertifikat);
        $this->assertSame('MAGANG/2026/0002', $certificates[1]->nomor_sertifikat);
        $this->assertSame('Telah menyelesaikan program magang.', $certificates[0]->deskripsi);
        $this->assertSame('Telah menyelesaikan program magang.', $certificates[1]->deskripsi);
        $this->assertSame(Sertifikat::PDF_STATUS_PENDING, $certificates[0]->pdf_status);
        $this->assertSame(Sertifikat::PDF_STATUS_PENDING, $certificates[1]->pdf_status);
        $this->assertSame(10, $certificates->count());
        Queue::assertPushedOn('certificate-pdfs', GenerateCertificatePdf::class);
        Queue::assertPushed(GenerateCertificatePdf::class, 10);

        $this->actingAs($admin)
            ->get(route('certificate-issues.create', ['batch' => 1]))
            ->assertOk()
            ->assertSee('10 sertifikat berhasil disimpan.')
            ->assertSee('Doni Prasetyo')
            ->assertSee('Peserta 10')
            ->assertSee('PDF sedang diproses')
            ->assertSee('data-pdf-status-pending', false)
            ->assertDontSee(route('certificate-issues.download', $certificates[0]));
        $this->actingAs($admin)
            ->post(route('certificate-issues.download-batch'), [
                'certificate_ids' => $certificates->pluck('id_sertifikat')->all(),
            ])
            ->assertRedirect(route('certificate-issues.create', ['batch' => 1]))
            ->assertSessionHasErrors('download');

        if (class_exists(ZipArchive::class)) {
            $this->mock(CertificatePdfService::class, function (MockInterface $mock) {
                $mock->shouldReceive('render')->times(10)->andReturn('%PDF-test');
            });
            foreach (Queue::pushed(GenerateCertificatePdf::class) as $job) {
                $job->handle(app(CertificatePdfService::class));
            }

            $this->actingAs($admin)
                ->get(route('certificate-issues.create', ['batch' => 1]))
                ->assertOk()
                ->assertSee('Unduh semua PDF (ZIP)');

            $response = $this->actingAs($admin)->post(route('certificate-issues.download-batch'), [
                'certificate_ids' => $certificates->pluck('id_sertifikat')->all(),
            ]);
            $response->assertOk()->assertDownload('sertifikat-batch.zip');
            $archivePath = $response->baseResponse->getFile()->getPathname();

            try {
                $archive = new ZipArchive;
                self::assertTrue($archive->open($archivePath));
                self::assertSame(10, $archive->numFiles);
                self::assertSame('MAGANG-2026-0001.pdf', $archive->getNameIndex(0));
                $archive->close();
            } finally {
                if (is_file($archivePath)) {
                    unlink($archivePath);
                }
            }
        }
    }

    public function test_internship_certificate_requires_a_duration(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-internship-validation-test',
            'password_hash' => 'password',
        ]);
        $participant = Peserta::query()->create([
            'nama' => 'Doni Prasetyo',
            'email' => 'doni@example.test',
        ]);
        $type = JenisSertifikat::query()->create([
            'kode' => 'MAGANG',
            'nama_jenis' => 'Magang',
            'aktif' => true,
        ]);
        $template = TemplateSertifikat::query()->create([
            'id_jenis' => $type->id_jenis,
            'nama_template' => 'Template Magang',
            'berkas_desain' => 'certificate-templates/background.png',
            'aktif' => true,
        ]);

        $this->actingAs($admin)->post(route('certificate-issues.store'), [
            'id_peserta' => [$participant->id_peserta],
            'id_jenis' => $type->id_jenis,
            'id_template' => $template->id_template,
            'tanggal_terbit' => '2026-10-28',
            'periode_mulai' => '2026-09-01',
            'periode_selesai' => '2026-11-30',
            'data_pendukung' => [
                'instansi' => 'PT Sukarobot',
                'posisi' => 'Software Developer',
            ],
            'deskripsi' => 'Telah menyelesaikan program magang.',
        ])->assertSessionHasErrors('data_pendukung.durasi');

        $this->assertDatabaseCount('sertifikat', 0);

        $competencyType = JenisSertifikat::query()->create([
            'kode' => 'UJIKOM',
            'nama_jenis' => 'Uji Kompetensi',
            'aktif' => true,
        ]);
        $competencyTemplate = TemplateSertifikat::query()->create([
            'id_jenis' => $competencyType->id_jenis,
            'nama_template' => 'Template Ujikom',
            'berkas_desain' => 'certificate-templates/ujikom.png',
            'aktif' => true,
        ]);

        $this->actingAs($admin)->post(route('certificate-issues.store'), [
            'id_peserta' => [$participant->id_peserta],
            'id_jenis' => $competencyType->id_jenis,
            'id_template' => $competencyTemplate->id_template,
            'tanggal_terbit' => '2026-10-28',
            'periode_mulai' => '2026-09-01',
            'periode_selesai' => '2026-11-30',
            'data_pendukung' => [
                'skema' => 'Teknisi Robotika',
                'bidang' => 'Otomasi dan robotika',
            ],
            'deskripsi' => 'Telah lulus uji kompetensi.',
        ])->assertSessionHasErrors('data_pendukung.nilai');

        $this->assertDatabaseCount('sertifikat', 0);
    }
}
