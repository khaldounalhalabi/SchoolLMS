<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->student = User::factory()->create(['role' => 'student']);
    }

    public function test_authenticated_user_can_submit_a_complaint(): void
    {
        Notification::fake();

        $this->actingAs($this->student)
            ->post(route('complaints.store'), [
                'subject' => 'Classroom issue',
                'description' => 'The classroom projector is not working.',
                'category' => 'technical',
                'priority' => 'high',
            ])
            ->assertRedirect(route('complaints.index'));

        $complaint = Complaint::firstOrFail();
        $this->assertSame($this->student->id, $complaint->submitted_by_user_id);
        $this->assertSame('pending', $complaint->status);
        Notification::assertSentTo($this->admin, SystemNotification::class);
    }

    public function test_admin_can_review_a_complaint(): void
    {
        $complaint = Complaint::create([
            'submitted_by_user_id' => $this->student->id,
            'subject' => 'Bus delay',
            'description' => 'The bus arrived late.',
            'category' => 'general',
            'priority' => 'normal',
            'status' => 'pending',
        ]);

        Notification::fake();

        $this->actingAs($this->admin)
            ->post(route('admin.complaints.review', $complaint), [
                'status' => 'resolved',
                'admin_response' => 'The transportation team has been notified.',
            ])
            ->assertRedirect(route('admin.complaints.index'));

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'resolved',
            'admin_response' => 'The transportation team has been notified.',
            'reviewed_by_user_id' => $this->admin->id,
        ]);
        Notification::assertSentTo($this->student, SystemNotification::class);
    }

    public function test_non_admin_cannot_review_a_complaint(): void
    {
        $complaint = Complaint::create([
            'submitted_by_user_id' => $this->student->id,
            'subject' => 'General issue',
            'description' => 'There is an issue.',
            'category' => 'general',
            'priority' => 'normal',
            'status' => 'pending',
        ]);

        $this->actingAs($this->student)
            ->post(route('admin.complaints.review', $complaint), [
                'status' => 'resolved',
                'admin_response' => 'Not allowed.',
            ])
            ->assertForbidden();
    }
}
