<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
    }
}
