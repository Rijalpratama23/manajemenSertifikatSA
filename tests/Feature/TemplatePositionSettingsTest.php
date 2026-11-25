<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JenisSertifikat;
use App\Models\TemplateSertifikat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplatePositionSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_settings_accept_decimal_commas_and_keep_hidden_element_coordinates(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-template-position-test',
            'password_hash' => 'password',
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
                'judul' => ['x' => 45.5, 'y' => 18.25, 'size' => 30, 'visible' => true],
                'diberikan' => ['x' => 62.5, 'y' => 35.25, 'size' => 13, 'visible' => true],
            ],
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('certificate-templates.edit', $template))
            ->assertOk()
            ->assertSee('type="text" inputmode="decimal"', false)
            ->assertSee('Periode 1 Nov 2026 – 23 Nov 2026')
            ->assertDontSee('Direktur Sukarobot Academy')
            ->assertDontSee('preview-sample-organization', false)
            ->assertDontSee('preview-sample-signature', false);

        $this->put(route('certificate-templates.update', $template), [
            'id_jenis' => $type->id_jenis,
            'nama_template' => 'Template Magang',
            'aktif' => '1',
            'posisi' => [
                'judul' => [
                    'x' => '50,3',
                    'y' => '44,7',
                    'size' => '31',
                    'visible' => '1',
                ],
                'diberikan' => ['visible' => '0'],
            ],
        ])->assertRedirect(route('certificate-templates.index'));

        $position = $template->fresh()->konfigurasi_posisi;
        $this->assertSame(50.3, $position['judul']['x']);
        $this->assertSame(44.7, $position['judul']['y']);
        $this->assertSame(62.5, $position['diberikan']['x']);
        $this->assertSame(35.25, $position['diberikan']['y']);
        $this->assertFalse($position['diberikan']['visible']);
    }
}
