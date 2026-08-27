<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
    }

    public function test_admin_can_start_impersonation(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/admin/impersonate', ['role' => 'teacher'])
            ->assertRedirect(route('dashboard'));

        $this->assertEquals('teacher', session('impersonate_role'));
        $this->assertEquals('read-only', session('impersonate_scope'));
        $this->assertDatabaseHas('impersonation_audits', [
            'admin_user_id' => $admin->id,
            'impersonated_role' => 'teacher',
            'action' => 'started',
        ]);
    }

    public function test_admin_can_stop_impersonation(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->withSession(['impersonate_role' => 'teacher'])
            ->post('/admin/stop-impersonate')
            ->assertRedirect(route('dashboard'));

        $this->assertNull(session('impersonate_role'));
    }

    /**
     * @dataProvider nonAdminRoleProvider
     */
    public function test_non_admin_cannot_start_impersonation(string $role): void
    {
        $user = $this->makeUser($role);

        $this->actingAs($user)->post('/admin/impersonate', ['role' => 'admin'])
            ->assertStatus(403);

        $this->assertNull(session('impersonate_role'));
    }

    /**
     * @dataProvider nonAdminRoleProvider
     */
    public function test_non_admin_cannot_stop_impersonation(string $role): void
    {
        $user = $this->makeUser($role);

        $this->actingAs($user)->post('/admin/stop-impersonate')
            ->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_when_starting_impersonation(): void
    {
        $this->post('/admin/impersonate', ['role' => 'admin'])
            ->assertRedirect(route('login'));

        $this->assertNull(session('impersonate_role'));
    }

    public function test_impersonating_admin_can_still_stop_impersonation(): void
    {
        $admin = $this->makeUser('admin');

        // Real role is admin, but effective role is currently overridden to teacher.
        $this->actingAs($admin)->withSession(['impersonate_role' => 'teacher'])
            ->post('/admin/stop-impersonate')
            ->assertRedirect(route('dashboard'));

        $this->assertNull(session('impersonate_role'));
    }

    public function test_impersonation_blocks_mutating_routes(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->withSession(['impersonate_role' => 'teacher'])
            ->post(route('teacher.attendance.store'))
            ->assertForbidden();
    }

    public function test_expired_impersonation_is_stopped_and_audited(): void
    {
        $admin = $this->makeUser('admin');
        $startedAt = now()->subMinutes(20);
        $expiresAt = now()->subMinutes(5);

        $this->actingAs($admin)
            ->withSession([
                'impersonate_role' => 'teacher',
                'impersonate_started_at' => $startedAt->timestamp,
                'impersonate_expires_at' => $expiresAt->timestamp,
                'impersonate_scope' => 'read-only',
            ])
            ->get(route('teacher.attendance'))
            ->assertRedirect(route('dashboard'));

        $this->assertNull(session('impersonate_role'));
        $this->assertDatabaseHas('impersonation_audits', [
            'admin_user_id' => $admin->id,
            'impersonated_role' => 'teacher',
            'action' => 'expired',
        ]);
    }

    public static function nonAdminRoleProvider(): array
    {
        return [
            'teacher' => ['teacher'],
            'student' => ['student'],
            'parent' => ['parent'],
        ];
    }
}
