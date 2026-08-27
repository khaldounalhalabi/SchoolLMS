<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\TuitionFee;
use App\Models\User;
use App\Services\Payment\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Event;
use Tests\TestCase;

class PaymentWebControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $parent;

    private User $student;

    private User $otherParent;

    private TuitionFee $tuitionFee;

    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create(['name' => 'Test School']);
        $this->academicYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
        ]);

        $grade = Grade::create([
            'school_id' => $school->id,
            'name' => 'Grade 8',
            'order_index' => 1,
        ]);

        $classroom = Classroom::create([
            'grade_id' => $grade->id,
            'name' => '8-A',
            'capacity' => 30,
        ]);

        $this->parent = User::factory()->create(['role' => 'parent']);
        $this->otherParent = User::factory()->create(['role' => 'parent']);
        $this->student = User::factory()->create(['role' => 'student', 'name' => 'Linked Child']);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'classroom_id' => $classroom->id,
            'enrollment_date' => '2025-09-01',
        ]);

        DB::table('parent_student')->insert([
            'parent_user_id' => $this->parent->id,
            'student_user_id' => $this->student->id,
            'relation' => 'father',
        ]);

        $this->tuitionFee = TuitionFee::create([
            'academic_year_id' => $this->academicYear->id,
            'amount' => 1000,
            'currency' => 'usd',
            'is_active' => true,
        ]);
    }

    public function test_parent_cannot_start_checkout_for_an_unlinked_student(): void
    {
        $unlinkedStudent = User::factory()->create(['role' => 'student']);

        $this->actingAs($this->parent)
            ->get(route('parent.payments.checkout', [
                'tuitionFee' => $this->tuitionFee,
                'student' => $unlinkedStudent->id,
            ]))
            ->assertRedirect(route('parent.payments.index'))
            ->assertSessionHas('error', 'Please select a valid student to pay for.');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_checkout_creates_a_pending_payment_and_redirects_to_stripe(): void
    {
        $session = Session::constructFrom([
            'id' => 'cs_test_123',
            'url' => 'https://checkout.stripe.test/cs_test_123',
        ]);

        $this->mock(StripeService::class, function ($mock) use ($session): void {
            $mock->shouldReceive('createCheckoutSession')
                ->once()
                ->andReturn($session);
        });

        $this->actingAs($this->parent)
            ->get(route('parent.payments.checkout', [
                'tuitionFee' => $this->tuitionFee,
                'student' => $this->student->id,
            ]))
            ->assertRedirect('https://checkout.stripe.test/cs_test_123');

        $this->assertDatabaseHas('payments', [
            'parent_user_id' => $this->parent->id,
            'student_user_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'pending',
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);
    }

    public function test_checkout_reuses_pending_session_url(): void
    {
        Payment::create([
            'parent_user_id' => $this->parent->id,
            'student_user_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'tuition_fee_id' => $this->tuitionFee->id,
            'amount' => $this->tuitionFee->amount,
            'currency' => $this->tuitionFee->currency,
            'status' => 'pending',
            'stripe_checkout_session_id' => 'cs_pending_123',
        ]);

        $session = Session::constructFrom([
            'id' => 'cs_pending_123',
            'url' => 'https://checkout.stripe.test/cs_pending_123',
        ]);

        $this->mock(StripeService::class, function ($mock) use ($session): void {
            $mock->shouldReceive('retrieveCheckoutSession')
                ->once()
                ->with('cs_pending_123')
                ->andReturn($session);
            $mock->shouldNotReceive('createCheckoutSession');
        });

        $this->actingAs($this->parent)
            ->get(route('parent.payments.checkout', [
                'tuitionFee' => $this->tuitionFee,
                'student' => $this->student->id,
            ]))
            ->assertRedirect('https://checkout.stripe.test/cs_pending_123');
    }

    public function test_test_payment_is_disabled_when_test_mode_is_off(): void
    {
        config(['services.stripe.test_mode' => false]);

        $this->actingAs($this->parent)
            ->post(route('parent.payments.test-process', $this->tuitionFee), [
                'student_user_id' => $this->student->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_local_test_payment_creates_a_succeeded_payment(): void
    {
        $response = $this->actingAs($this->parent)
            ->post(route('parent.payments.test-process', $this->tuitionFee), [
                'student_user_id' => $this->student->id,
            ]);

        $sessionId = Payment::query()->value('stripe_checkout_session_id');

        $response->assertRedirect(route('parent.payments.success', [
            'session_id' => $sessionId,
        ]));

        $this->assertDatabaseHas('payments', [
            'parent_user_id' => $this->parent->id,
            'student_user_id' => $this->student->id,
            'status' => 'succeeded',
        ]);
    }

    public function test_payment_success_page_is_scoped_to_the_authenticated_parent(): void
    {
        $payment = Payment::create([
            'parent_user_id' => $this->otherParent->id,
            'student_user_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'tuition_fee_id' => $this->tuitionFee->id,
            'amount' => $this->tuitionFee->amount,
            'currency' => $this->tuitionFee->currency,
            'status' => 'succeeded',
            'stripe_checkout_session_id' => 'cs_other_parent',
            'stripe_payment_intent_id' => 'pi_other_parent',
            'paid_at' => now(),
        ]);

        $this->actingAs($this->parent)
            ->get(route('parent.payments.success', ['session_id' => $payment->stripe_checkout_session_id]))
            ->assertRedirect(route('parent.payments.index'))
            ->assertSessionHas('error', 'Payment session not found.');
    }

    public function test_checkout_completed_webhook_is_idempotent(): void
    {
        $payment = Payment::create([
            'parent_user_id' => $this->parent->id,
            'student_user_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'tuition_fee_id' => $this->tuitionFee->id,
            'amount' => $this->tuitionFee->amount,
            'currency' => $this->tuitionFee->currency,
            'status' => 'pending',
            'stripe_checkout_session_id' => 'cs_webhook_123',
        ]);

        $event = Event::constructFrom([
            'id' => 'evt_checkout_completed',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_webhook_123',
                    'payment_status' => 'paid',
                    'payment_intent' => 'pi_webhook_123',
                ],
            ],
        ]);

        $this->mock(StripeService::class, function ($mock) use ($event): void {
            $mock->shouldReceive('constructWebhookEvent')
                ->twice()
                ->andReturn($event);
        });

        $headers = ['Stripe-Signature' => 'test-signature'];

        $this->post('/stripe/webhook', [], $headers)->assertOk();
        $this->post('/stripe/webhook', [], $headers)->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'succeeded',
            'stripe_payment_intent_id' => 'pi_webhook_123',
        ]);
    }
}
