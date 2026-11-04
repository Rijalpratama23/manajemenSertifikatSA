<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JenisSertifikat;
use App\Models\Peserta;
use App\Models\TemplateSertifikat;
use App\Services\CertificatePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class CertificateIssuanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_issue_certificate_and_download_its_private_pdf(): void
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
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('certificate-issues.create'))
            ->assertOk()
            ->assertSee('Terbitkan Sertifikat')
            ->assertSee('Doni Prasetyo')
            ->assertSee('Template Magang');

        $this->mock(CertificatePdfService::class, function (MockInterface $mock) {
            $mock->shouldReceive('render')->once()->andReturn('%PDF-test');
        });

        $response = $this->actingAs($admin)->post(route('certificate-issues.store'), [
            'id_peserta' => $participant->id_peserta,
            'id_jenis' => $type->id_jenis,
            'id_template' => $template->id_template,
            'tanggal_terbit' => '2026-10-28',
            'periode_mulai' => '2026-09-01',
            'periode_selesai' => '2026-11-30',
            'durasi' => '3 bulan',
            'nilai' => '92',
            'predikat' => 'Sangat Baik',
        ]);

        $response->assertRedirect(route('certificate-issues.create'));
        $certificate = \App\Models\Sertifikat::query()->firstOrFail();
        $this->assertSame('MAGANG/2026/0001', $certificate->nomor_sertifikat);
        $this->assertSame($admin->id_admin, $certificate->id_admin);
        $this->assertSame($template->id_template, $certificate->id_template);
        Storage::disk('local')->assertExists($certificate->berkas_pdf);

        $this->actingAs($admin)
            ->get(route('certificate-issues.create'))
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
            ->assertDontSee($participant->nama)
            ->assertDontSee($participant->email);
    }

    public function test_issue_page_requires_an_authenticated_admin(): void
    {
        $this->get(route('certificate-issues.create'))->assertRedirect(route('login'));
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
        ]);

        $response->assertSessionHasErrors('periode_selesai');
        $this->assertDatabaseCount('sertifikat', 0);
        $this->assertDatabaseCount('nomor_urut', 0);
    }
}
