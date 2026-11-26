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

    public function test_participant_table_filters_can_update_without_an_apply_button(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-participant-filter-test',
            'password_hash' => 'password',
        ]);
        foreach (range(1, 12) as $number) {
            $participant = Peserta::query()->create(['nama' => sprintf('Peserta %02d', $number)]);
            $participant->setCreatedAt(now()->subDays(12 - $number));
            $participant->saveQuietly();
        }

        $this->actingAs($admin)
            ->get(route('participants.index'))
            ->assertOk()
            ->assertSee('data-participant-filter-form', false)
            ->assertSee('Menampilkan 1–10 dari 12 peserta')
            ->assertSee('Navigasi halaman data peserta')
            ->assertSee('Halaman berikutnya')
            ->assertDontSee('Showing')
            ->assertDontSee('results')
            ->assertDontSee('Terapkan');

        $oldestResponse = $this->getJson(route('participants.index', [
            'per_page' => 25,
            'sort' => 'oldest',
            'partial' => 1,
        ]));
        $oldestResponse
            ->assertOk()
            ->assertJsonPath('summary', 'Menampilkan 12 dari 12 peserta');
        $this->assertLessThan(
            strpos($oldestResponse->json('table'), 'Peserta 12'),
            strpos($oldestResponse->json('table'), 'Peserta 01'),
        );

        $newestResponse = $this->getJson(route('participants.index', [
            'per_page' => 10,
            'sort' => 'newest',
            'partial' => 1,
        ]));
        $newestResponse
            ->assertOk()
            ->assertJsonPath('summary', 'Menampilkan 10 dari 12 peserta');
        $this->assertStringContainsString('Peserta 12', $newestResponse->json('table'));
        $this->assertStringNotContainsString('Peserta 01', $newestResponse->json('table'));
    }
}
