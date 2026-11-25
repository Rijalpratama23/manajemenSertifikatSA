<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Peserta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_search_suggests_names_filters_without_page_navigation_and_restores_all_results_when_cleared(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-participant-search-test',
            'password_hash' => 'password',
        ]);
        Peserta::query()->create(['nama' => 'Alina Putri']);
        Peserta::query()->create(['nama' => 'Budi Santoso']);

        $this->actingAs($admin)
            ->get(route('participants.index'))
            ->assertOk()
            ->assertSee('data-participant-search-suggestions', false)
            ->assertSee('Alina Putri')
            ->assertSee('Budi Santoso');

        $filteredResponse = $this->getJson(route('participants.index', [
            'search' => 'Al',
            'partial' => 1,
        ]));
        $filteredResponse
            ->assertOk()
            ->assertJsonPath('summary', 'Menampilkan 1 dari 1 peserta');
        $this->assertStringContainsString('Alina Putri', $filteredResponse->json('table'));
        $this->assertStringNotContainsString('Budi Santoso', $filteredResponse->json('table'));

        $clearedResponse = $this->getJson(route('participants.index', ['partial' => 1]));
        $clearedResponse
            ->assertOk()
            ->assertJsonPath('summary', 'Menampilkan 2 dari 2 peserta');
        $this->assertStringContainsString('Alina Putri', $clearedResponse->json('table'));
        $this->assertStringContainsString('Budi Santoso', $clearedResponse->json('table'));
    }
}
