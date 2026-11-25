<?php

namespace Tests\Feature;

use App\Imports\PesertaImport;
use App\Jobs\GenerateCertificatePdf;
use App\Models\Admin;
use App\Models\JenisSertifikat;
use App\Models\Peserta;
use App\Models\Sertifikat;
use App\Models\TemplateSertifikat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class PesertaCertificateTypeFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_types_combines_regular_and_course_participants(): void
    {
        Schema::create('kursus', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nis');
            $table->string('nama');
            $table->integer('id_level')->nullable();
            $table->integer('id_sertifikat')->nullable();
        });

        DB::table('kursus')->insert([
            ['nis' => 202300001, 'nama' => 'Peserta Kursus Satu'],
            ['nis' => 202300002, 'nama' => 'Peserta Kursus Dua'],
        ]);

        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-all-types-filter-test',
            'password_hash' => 'password',
        ]);
        Peserta::query()->create([
            'nama' => 'Peserta Umum',
            'email' => 'umum@example.test',
            'asal_instansi' => 'Universitas Contoh',
        ]);

        $this->actingAs($admin)
            ->get(route('participants.index'))
            ->assertOk()
            ->assertSee('Peserta Umum')
            ->assertSee('Peserta Kursus Satu')
            ->assertSee('Peserta Kursus Dua')
            ->assertViewHas('participants', function ($participants) {
                return $participants->total() === 3;
            });

        Schema::dropIfExists('kursus');
    }

    public function test_course_type_reads_participants_from_course_table(): void
    {
        Schema::create('kursus', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nis');
            $table->string('nama');
            $table->integer('id_level')->nullable();
            $table->integer('id_sertifikat')->nullable();
        });

        DB::table('kursus')->insert([
            ['nis' => 202300001, 'nama' => 'Peserta Kursus Satu'],
            ['nis' => 202300002, 'nama' => 'Peserta Kursus Dua'],
        ]);

        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-course-filter-test',
            'password_hash' => 'password',
        ]);
        $courseType = $this->createCertificateType('KURSUS', 'Kursus');

        $this->actingAs($admin)
            ->get(route('participants.index', ['jenis' => $courseType->id_jenis]))
            ->assertOk()
            ->assertSee('Peserta Kursus Satu')
            ->assertSee('202300001')
            ->assertSee('Peserta Kursus Dua')
            ->assertSee('NIS');

        Schema::dropIfExists('kursus');
    }

    public function test_legacy_course_type_code_34_appears_in_submenu_and_uses_course_participants(): void
    {
        Schema::create('kursus', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nis');
            $table->string('nama');
            $table->integer('id_level')->nullable();
            $table->integer('id_sertifikat')->nullable();
        });

        DB::table('kursus')->insert([
            ['nis' => 202300001, 'nama' => 'Peserta Kursus Lama'],
        ]);

        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-legacy-course-filter-test',
            'password_hash' => 'password',
        ]);
        $courseType = $this->createCertificateType('34', 'kursus');

        $this->actingAs($admin)
            ->get(route('participants.index', ['jenis' => $courseType->id_jenis]))
            ->assertOk()
            ->assertSee('Peserta Kursus Lama')
            ->assertSee('kursus')
            ->assertSee(route('participants.index', ['jenis' => $courseType->id_jenis]), false)
            ->assertViewHas('isCourseView', true);

        Schema::dropIfExists('kursus');
    }

    public function test_course_participants_sync_to_the_certificate_issuance_list(): void
    {
        Schema::create('kursus', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nis');
            $table->string('nama');
            $table->integer('id_level')->nullable();
            $table->integer('id_sertifikat')->nullable();
        });
        DB::table('kursus')->insert([
            'nis' => 202400001,
            'nama' => 'Siswa Kursus Baru',
        ]);

        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-course-issuance-sync-test',
            'password_hash' => 'password',
        ]);
        $courseType = $this->createCertificateType('KURSUS', 'Kursus');
        $template = $this->createTemplate($courseType);

        $this->actingAs($admin)
            ->get(route('certificate-issues.create'))
            ->assertOk()
            ->assertSee('Siswa Kursus Baru')
            ->assertSee('Data lahir belum diisi (opsional)')
            ->assertSee('Tahun ajaran')
            ->assertSee('data-course-participants="true"', false);

        $participant = Peserta::query()->where('nis', 202400001)->firstOrFail();
        $this->assertNull($participant->email);

        Queue::fake();
        $this->post(route('certificate-issues.store'), [
            'id_peserta' => [$participant->id_peserta],
            'id_jenis' => $courseType->id_jenis,
            'id_template' => $template->id_template,
            'tanggal_terbit' => '2026-11-25',
            'periode_mulai' => '2026-11-01',
            'periode_selesai' => '2026-11-25',
            'data_pendukung' => [
                'nama_kursus' => 'Pemrograman Dasar',
                'tahun_ajaran' => '2025-2026',
                'prestasi' => 'Baik',
                'level' => 'Basic 3',
                'kota_terbit' => 'Sukabumi',
                'durasi' => '24 pertemuan',
            ],
            'deskripsi' => '',
        ])->assertRedirect(route('certificate-issues.create', ['batch' => 1]));

        $certificate = Sertifikat::query()->firstOrFail();
        $this->assertDatabaseHas('sertifikat', [
            'id_peserta' => $participant->id_peserta,
            'id_jenis' => $courseType->id_jenis,
            'id_template' => $template->id_template,
            'deskripsi' => 'Telah menyelesaikan pembelajaran Pemrograman Dasar tahun 2025-2026 dengan prestasi Baik pada level Basic 3.',
        ]);
        Queue::assertPushed(GenerateCertificatePdf::class);

        $pdfHtml = view('certificates.pdf', [
            'sertifikat' => $certificate->load(['peserta', 'jenis', 'template']),
            'background' => 'data:image/png;base64,',
            'qrCode' => 'data:image/png;base64,',
        ])->render();
        $this->assertStringNotContainsString('Tempat, Tgl. Lahir:', $pdfHtml);
        $this->assertStringContainsString('Nomor Induk: 202400001', $pdfHtml);
        $this->assertStringContainsString('Sukabumi, 25 November 2026', $pdfHtml);
        $this->assertStringContainsString('Area foto 3 x 4', $pdfHtml);
        $this->assertStringContainsString('left: 74.25mm; top: 161.70mm', $pdfHtml);

        $participant->update([
            'tempat_lahir' => 'Sukabumi',
            'tanggal_lahir' => '2008-06-14',
        ]);
        $pdfWithBirthDetails = view('certificates.pdf', [
            'sertifikat' => $certificate->load(['peserta', 'jenis', 'template']),
            'background' => 'data:image/png;base64,',
            'qrCode' => 'data:image/png;base64,',
        ])->render();
        $this->assertStringContainsString(
            'Tempat, Tgl. Lahir: Sukabumi, 14 Juni 2008',
            $pdfWithBirthDetails,
        );

        Schema::dropIfExists('kursus');
    }

    public function test_admin_can_create_participant_identity_for_course_certificates(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-course-participant-identity-test',
            'password_hash' => 'password',
        ]);

        $this->actingAs($admin)
            ->post(route('participants.store'), [
                'nama' => 'Siswa Baru',
                'nis' => '202500001',
                'tempat_lahir' => 'Sukabumi',
                'tanggal_lahir' => '2009-03-21',
                'email' => '',
                'asal_instansi' => '',
            ])
            ->assertRedirect(route('participants.index'));

        $participant = Peserta::query()->where('nis', 202500001)->firstOrFail();
        $this->assertSame('Siswa Baru', $participant->nama);
        $this->assertSame('Sukabumi', $participant->tempat_lahir);
        $this->assertSame('2009-03-21', $participant->tanggal_lahir->toDateString());
    }

    public function test_admin_profile_edits_keep_course_roster_nis_and_name_synchronized(): void
    {
        Schema::create('kursus', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nis');
            $table->string('nama');
            $table->integer('id_level')->nullable();
            $table->integer('id_sertifikat')->nullable();
        });
        DB::table('kursus')->insert([
            'nis' => 202500003,
            'nama' => 'Siswa Kursus',
        ]);
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-course-profile-edit-test',
            'password_hash' => 'password',
        ]);
        $courseType = $this->createCertificateType('KURSUS', 'Kursus');

        $this->actingAs($admin)
            ->get(route('participants.index', ['jenis' => $courseType->id_jenis]))
            ->assertOk();
        $participant = Peserta::query()->where('nis', 202500003)->firstOrFail();

        $this->put(route('participants.update', $participant), [
            'nama' => 'Nama Diperbarui',
            'nis' => '202500004',
            'tempat_lahir' => 'Sukabumi',
            'tanggal_lahir' => '2008-05-10',
            'email' => '',
            'asal_instansi' => '',
        ])->assertRedirect(route('participants.index'));

        $this->assertDatabaseHas('kursus', [
            'nis' => 202500004,
            'nama' => 'Nama Diperbarui',
        ]);
        $this->assertDatabaseHas('peserta', [
            'id_peserta' => $participant->id_peserta,
            'nis' => 202500004,
            'nama' => 'Nama Diperbarui',
            'tempat_lahir' => 'Sukabumi',
        ]);

        Schema::dropIfExists('kursus');
    }

    public function test_admin_can_import_course_identity_by_nis_without_overwriting_existing_birth_data(): void
    {
        $participant = Peserta::query()->create([
            'nis' => 202500002,
            'nama' => 'Siswa Lama',
            'tempat_lahir' => 'Cianjur',
            'tanggal_lahir' => '2008-04-02',
            'email' => null,
        ]);

        $import = new PesertaImport;
        $import->collection(collect([
            ['', 'Siswa Diperbarui', '', '', '202500002', '', ''],
        ]));

        $participant->refresh();
        $this->assertSame('Siswa Diperbarui', $participant->nama);
        $this->assertSame('Cianjur', $participant->tempat_lahir);
        $this->assertSame('2008-04-02', $participant->tanggal_lahir->toDateString());
    }

    public function test_admin_can_filter_participants_by_certificate_type_while_searching(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-participant-filter-test',
            'password_hash' => 'password',
        ]);
        $magang = $this->createCertificateType('MAGANG', 'Magang');
        $seminar = $this->createCertificateType('SEMINAR', 'Seminar');
        $magangTemplate = $this->createTemplate($magang);
        $seminarTemplate = $this->createTemplate($seminar);

        $participantWithBothTypes = $this->createParticipant('Peserta Magang', 'magang@example.test');
        $this->createCertificate($participantWithBothTypes, $magang, $magangTemplate, $admin, 1);
        $this->createCertificate($participantWithBothTypes, $seminar, $seminarTemplate, $admin, 2);

        $participantWithSeminarOnly = $this->createParticipant('Peserta Seminar', 'seminar@example.test');
        $this->createCertificate($participantWithSeminarOnly, $seminar, $seminarTemplate, $admin, 3);
        $this->createParticipant('Peserta Tanpa Sertifikat', 'tanpa@example.test');

        $this->actingAs($admin)
            ->get(route('participants.index', [
                'jenis' => $magang->id_jenis,
                'search' => 'Peserta',
                'per_page' => 25,
                'sort' => 'oldest',
            ]))
            ->assertOk()
            ->assertSee('Peserta Magang')
            ->assertDontSee('Peserta Seminar')
            ->assertDontSee('Peserta Tanpa Sertifikat')
            ->assertSee('Semua Jenis')
            ->assertSee('Magang')
            ->assertSee('NIS')
            ->assertSee('Asal Sekolah/Universitas')
            ->assertDontSee('<th scope="col">Email</th>', false)
            ->assertSee(route('participants.index', ['jenis' => $magang->id_jenis]), false)
            ->assertDontSee(route('participants.index', ['jenis' => $seminar->id_jenis]), false)
            ->assertSee('name="jenis" value="'.$magang->id_jenis.'"', false)
            ->assertViewHas('participants', function ($participants) use ($participantWithBothTypes) {
                return $participants->total() === 1
                    && $participants->first()->is($participantWithBothTypes)
                    && $participants->first()->sertifikat_count === 2;
            });
    }

    private function createParticipant(string $name, string $email): Peserta
    {
        return Peserta::query()->create([
            'nama' => $name,
            'email' => $email,
            'asal_instansi' => 'Universitas Contoh',
        ]);
    }

    private function createCertificateType(string $code, string $name): JenisSertifikat
    {
        return JenisSertifikat::query()->create([
            'kode' => $code,
            'nama_jenis' => $name,
            'aktif' => true,
        ]);
    }

    private function createTemplate(JenisSertifikat $type): TemplateSertifikat
    {
        return TemplateSertifikat::query()->create([
            'id_jenis' => $type->id_jenis,
            'nama_template' => 'Template '.$type->nama_jenis,
            'berkas_desain' => 'certificate-templates/'.$type->kode.'.png',
            'aktif' => true,
        ]);
    }

    private function createCertificate(
        Peserta $participant,
        JenisSertifikat $type,
        TemplateSertifikat $template,
        Admin $admin,
        int $number,
    ): Sertifikat {
        return Sertifikat::query()->create([
            'nomor_sertifikat' => $type->kode.'/2026/'.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
            'token_publik' => str_pad((string) $number, 64, 'a', STR_PAD_LEFT),
            'kode_verifikasi' => str_pad((string) $number, 12, '0', STR_PAD_LEFT),
            'id_peserta' => $participant->id_peserta,
            'id_jenis' => $type->id_jenis,
            'id_admin' => $admin->id_admin,
            'id_template' => $template->id_template,
            'tanggal_terbit' => '2026-11-16',
            'status' => Sertifikat::STATUS_AKTIF,
        ]);
    }
}
