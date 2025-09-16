<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Notifications\CodeVerifyNotification;
use Illuminate\Support\Facades\Notification;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registration_sends_verification_email()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, CodeVerifyNotification::class);
    }

    /** @test */
    public function redirect_from_verify_notice_to_verify_code()
    {
        $user = User::factory()->create([
        'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertStatus(200)
            ->assertSee('認証はこちらから');

        $this->actingAs($user)
            ->get(route('verification.code.form'))
            ->assertStatus(200)
            ->assertViewIs('auth.verify_code');
    }

    /** @test */
    public function email_verification_redirects_to_items_list()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $code = session('verification_code') ?? 1234;
        session(['verification_code' => $code, 'registered_user' => $user->id]);

        $this->actingAs($user)
            ->post(route('verification.code.submit'), [
                'code' => [$code],
            ])
            ->assertRedirect(route('attendance'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}