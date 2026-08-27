<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_only_their_notifications(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $otherUser = User::factory()->create(['role' => 'student']);

        $user->notify(new SystemNotification('For me', 'Private message', route('dashboard')));
        $otherUser->notify(new SystemNotification('Not for me', 'Private message', route('dashboard')));

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('For me')
            ->assertDontSee('Not for me');
    }

    public function test_notifications_are_translated_using_the_current_locale(): void
    {
        $user = User::factory()->create(['role' => 'parent']);
        $user->notify(new SystemNotification(
            'Student linked',
            ':student is now linked to your account.',
            null,
            'relationship',
            ['student' => 'Student7'],
        ));

        app()->setLocale('ar');

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('تم ربط الطالب')
            ->assertSee('تم الآن ربط Student7 بحسابك.');
    }

    public function test_user_can_mark_a_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->notify(new SystemNotification('Read me', 'Message', route('dashboard')));
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->post(route('notifications.read', $notification))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $otherUser = User::factory()->create(['role' => 'student']);
        $otherUser->notify(new SystemNotification('Private', 'Message'));
        $notification = $otherUser->notifications()->firstOrFail();

        $this->actingAs($user)
            ->post(route('notifications.read', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->notify(new SystemNotification('First', 'Message'));
        $user->notify(new SystemNotification('Second', 'Message'));

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }
}
