<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_shows_a_success_notice_after_redirect(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-interaction',
            'password_hash' => 'correct-password',
        ]);

        $this->post(route('login.store'), [
            'username' => $admin->username,
            'password' => 'correct-password',
        ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('auth_notice', [
                'type' => 'success',
                'message' => 'Login berhasil. Selamat datang kembali.',
            ]);
    }

    public function test_failed_login_shows_a_popup_notice_and_inline_validation_error(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'username' => 'unknown-admin',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username')
            ->assertSessionHas('auth_notice', [
                'type' => 'error',
                'message' => 'Login gagal. Periksa kembali username dan password Anda.',
            ]);
    }

    public function test_logout_requires_a_confirmation_dialog_and_shows_a_notice_after_logout(): void
    {
        $admin = Admin::query()->create([
            'nama' => 'Admin Test',
            'username' => 'admin-logout-interaction',
            'password_hash' => 'correct-password',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-confirm-title="Keluar dari akun?"', false)
            ->assertSee('data-confirm-label="Ya, Keluar"', false);

        $this->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_notice', [
                'type' => 'success',
                'message' => 'Anda berhasil keluar dari akun.',
            ]);
    }
}
