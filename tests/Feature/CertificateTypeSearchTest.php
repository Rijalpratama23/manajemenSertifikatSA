<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JenisSertifikat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTypeSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_type_search_suggests_all_names_and_filters_matching_results(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-certificate-type-search-test',
            'password_hash' => 'password',
        ]);
        JenisSertifikat::query()->create([
            'kode' => 'LPK',
            'nama_jenis' => 'Pelatihan LPK',
            'aktif' => true,
        ]);
        JenisSertifikat::query()->create([
            'kode' => 'MAG',
            'nama_jenis' => 'Magang',
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('certificate-types.index', ['search' => 'Pelatihan']))
            ->assertOk()
            ->assertSee('autocomplete="off"', false)
            ->assertDontSee('<datalist', false)
            ->assertSee('data-certificate-type-search-suggestions', false)
            ->assertSee('data-certificate-type-search-suggestion', false)
            ->assertSee('Pelatihan LPK')
            ->assertSee('Magang')
            ->assertViewHas('jenisSertifikat', fn ($types) => (
                $types->total() === 1
                && $types->first()->nama_jenis === 'Pelatihan LPK'
            ));

        $partialResponse = $this->getJson(route('certificate-types.index', [
            'search' => 'Pelatihan',
            'partial' => 1,
        ]));
        $partialResponse
            ->assertOk()
            ->assertJsonPath('summary', 'Menampilkan 1 dari 1 jenis sertifikat');
        $this->assertStringContainsString('Pelatihan LPK', $partialResponse->json('table'));
        $this->assertStringNotContainsString('Magang', $partialResponse->json('table'));

        $clearedResponse = $this->getJson(route('certificate-types.index', ['partial' => 1]));
        $clearedResponse
            ->assertOk()
            ->assertJsonPath('summary', 'Menampilkan 2 dari 2 jenis sertifikat');
        $this->assertStringContainsString('Pelatihan LPK', $clearedResponse->json('table'));
        $this->assertStringContainsString('Magang', $clearedResponse->json('table'));
    }
}
