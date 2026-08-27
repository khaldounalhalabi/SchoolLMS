<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role = 'admin', bool $active = true): User
    {
        return User::factory()->create([
            'role' => $role,
            'password' => Hash::make('password'),
            'is_active' => $active,
        ]);
    }

    // ---------------------------------------------------------------
    // UC-01: Login
    // ---------------------------------------------------------------

    public function test_login_success_returns_200_with_token_and_user(): void
    {
        $user = $this->makeUser('admin');

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'email', 'role']]]);
    }

    public function test_login_wrong_password_returns_422(): void
    {
        $user = $this->makeUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_inactive_user_returns_403(): void
    {
        $user = $this->makeUser('teacher', false);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // UC-02: Logout
    // ---------------------------------------------------------------

    public function test_logout_invalidates_token(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('api-token')->plainTextToken;

        $this->withToken($token)->postJson('/api/auth/logout')
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Logged out successfully.']);

        // Token should be deleted from the database
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    // ---------------------------------------------------------------
    // UC-05: Role-based access
    // ---------------------------------------------------------------

    public function test_teacher_jwt_blocked_on_admin_only_route_returns_403(): void
    {
        $teacher = $this->makeUser('teacher');

        $this->actingAs($teacher)->getJson('/api/users')
            ->assertStatus(403);
    }

    public function test_parent_jwt_blocked_on_teacher_route_returns_403(): void
    {
        $parent = $this->makeUser('parent');

        $this->actingAs($parent)->postJson('/api/v1/behavioral-notes', [
            'student_user_id' => 99,
            'note' => 'Test',
            'severity' => 'info',
            'date' => now()->toDateString(),
        ])->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // UC-03: Password recovery
    // ---------------------------------------------------------------

    public function test_forgot_password_sends_mail_for_existing_email(): void
    {
        Notification::fake();

        $user = $this->makeUser();

        $this->postJson('/api/auth/password/forgot', [
            'email' => $user->email,
        ])->assertStatus(200);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_returns_422_for_unknown_email(): void
    {
        $response = $this->postJson('/api/auth/password/forgot', [
            'email' => 'nobody@nowhere.test',
        ]);

        $response->assertStatus(422);
    }

    // ---------------------------------------------------------------
    // UC-06: Create user (admin only)
    // ---------------------------------------------------------------

    public function test_admin_can_create_user(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->postJson('/api/auth/register', [
            'name' => 'New Teacher',
            'email' => 'new.teacher@school.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'teacher',
        ])->assertStatus(201)
            ->assertJsonPath('data.user.role', 'teacher');
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $teacher = $this->makeUser('teacher');

        $this->actingAs($teacher)->postJson('/api/auth/register', [
            'name' => 'Attacker',
            'email' => 'attacker@school.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin',
        ])->assertStatus(403);
    }
}
