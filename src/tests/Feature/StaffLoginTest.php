<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_is_required_for_staff_login()
    {
        $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password123',
        ])
        ->assertRedirect('/login')
        ->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function password_is_required_for_staff_login()
    {
        $this->from('/login')->post('/login', [
            'email' => 'staff@example.com',
            'password' => '',
        ])
        ->assertRedirect('/login')
        ->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function login_fails_if_user_is_admin()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => 1,
        ]);

        $this->from('/login')->post('/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ])
        ->assertRedirect('/login')
        ->assertSessionHasErrors([
            'email' => '管理者ログインが必要です。',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function staff_can_login_with_valid_credentials()
    {
        $staff = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => 0,
        ]);

        $response = $this->post('/login', [
            'email' => $staff->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('attendance'));
        $this->assertAuthenticatedAs($staff);
    }
}