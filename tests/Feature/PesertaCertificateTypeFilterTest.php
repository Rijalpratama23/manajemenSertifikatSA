<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JenisSertifikat;
use App\Models\Peserta;
use App\Models\Sertifikat;
use App\Models\TemplateSertifikat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
