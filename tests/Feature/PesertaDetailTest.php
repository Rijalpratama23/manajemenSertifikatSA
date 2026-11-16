<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Peserta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesertaDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_a_participant_detail_page_without_certificates(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-participant-detail-test',
            'password_hash' => 'password',
        ]);
        $participant = Peserta::query()->create([
            'nama' => 'Rijal Pratama',
            'email' => 'rijal@example.test',
            'asal_instansi' => 'Universitas Nusa Putra',
        ]);

        $this->actingAs($admin)
            ->get(route('participants.show', $participant))
            ->assertOk()
            ->assertSee('Detail Peserta')
            ->assertSee('Rijal Pratama')
            ->assertSee('Universitas Nusa Putra')
            ->assertSee('Jumlah sertifikat')
            ->assertSee('Peserta ini belum memiliki sertifikat.')
            ->assertSee(route('participants.edit', $participant));
    }
}
