<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function name_is_required_shows_error_message()
    {
        $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /** @test */
    public function email_is_required_shows_error_message()
    {
        $this->from('/register')->post('/register', [
            'name' => '山田太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function password_is_required_shows_error_message()
    {
        $this->from('/register')->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function password_must_be_at_least_8_characters()
    {
        $this->from('/register')->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /** @test */
    public function password_and_confirmation_must_match()
    {
        $this->from('/register')->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません',
        ]);
    }

    /** @test */
    public function user_can_register_with_valid_data_and_is_logged_out_afterwards()
    {
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'is_admin' => 0,
        ]);

        $this->assertGuest();

        $response->assertRedirect(route('verification.notice'));

        $this->assertNotNull(session('verification_code'));
        $this->assertEquals(User::first()->id, session('registered_user'));
    }
}