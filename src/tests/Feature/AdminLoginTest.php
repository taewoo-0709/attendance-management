<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_is_required_for_admin_login()
    {
        $this->from('/admin/login')->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ])
        ->assertRedirect('/admin/login')
        ->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function password_is_required_for_admin_login()
    {
        $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ])
        ->assertRedirect('/admin/login')
        ->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function login_fails_if_user_is_staff()
    {
        $staff = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => 0,
        ]);

        $this->from('/admin/login')->post('/admin/login', [
            'email' => $staff->email,
            'password' => 'password123',
        ])
        ->assertRedirect('/admin/login')
        ->assertSessionHasErrors([
            'email' => '管理者権限がありません。',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function admin_can_login_with_valid_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.attendance.list'));
        $this->assertAuthenticatedAs($admin);
    }
}