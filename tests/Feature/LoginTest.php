<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_properly(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Berkah Mandiri Inventory');
        $response->assertSee('Masuk ke Akun');
        $response->assertSee('Alamat Email');
        $response->assertSee('Kata Sandi');
        $response->assertSee('Ingat sesi saya');
        $response->assertSee('Masuk ke Sistem');
        $response->assertSee('Sedang Masuk...');
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'budi@test.com')
            ->set('password', 'password123')
            ->set('remember', true)
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_authenticate_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'budi@test.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_email_and_password_are_required(): void
    {
        Livewire::test(Login::class)
            ->set('email', '')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['email' => 'required', 'password' => 'required']);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create([
            'name' => 'User Nonaktif',
            'email' => 'nonaktif@test.com',
            'password' => bcrypt('password123'),
            'role' => 'petugas',
            'is_active' => false,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'nonaktif@test.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_authenticated_user_redirected_from_login(): void
    {
        $user = User::create([
            'name' => 'Active Admin',
            'email' => 'active@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(Login::class)
            ->assertRedirect(route('dashboard'));
    }
}
