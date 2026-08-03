<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ErrorLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_found_page_uses_clear_indonesian_message(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('hubungi pengelola Sistem Informasi BK');
    }

    public function test_forbidden_page_uses_clear_indonesian_message(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Counselor,
            'must_change_password' => false,
        ]);

        $this->actingAs($user)
            ->get('/app/cms')
            ->assertForbidden()
            ->assertSee('Akses tidak diizinkan')
            ->assertDontSee('This action is unauthorized');
    }

    public function test_framework_messages_have_indonesian_translations(): void
    {
        $this->assertSame('Identitas masuk atau kata sandi tidak sesuai.', __('auth.failed'));
        $this->assertSame('Kata sandi berhasil diperbarui.', __('passwords.reset'));
        $this->assertSame('&laquo; Sebelumnya', __('pagination.previous'));
    }

    public function test_expired_session_page_explains_the_next_step(): void
    {
        $html = Blade::render("@include('errors.419')");

        $this->assertStringContainsString('Sesi Anda telah berakhir', $html);
        $this->assertStringContainsString('Masuk kembali', $html);
    }

    public function test_upload_limit_page_explains_file_limits_in_indonesian(): void
    {
        $html = Blade::render("@include('errors.413')");

        $this->assertStringContainsString('Ukuran berkas terlalu besar', $html);
        $this->assertStringContainsString('maksimal 10 berkas', $html);
        $this->assertStringContainsString('10 MB', $html);
    }
}
