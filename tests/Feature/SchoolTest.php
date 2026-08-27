<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_year_creation_redirects_to_school_setup_when_no_school_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.academic-years.store'), [
                'name' => '2026-2027',
                'start_date' => '2026-09-01',
                'end_date' => '2027-06-30',
            ])
            ->assertRedirect(route('admin.schools.index'))
            ->assertSessionHas('error', 'Create a school before adding an academic year.');

        $this->assertDatabaseCount('schools', 0);
        $this->assertDatabaseCount('academic_years', 0);
    }

    public function test_admin_can_create_a_school(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.schools.store'), [
                'name' => 'Al-Nahda School',
                'address' => 'Main Street',
                'phone' => '+962700000000',
            ])
            ->assertRedirect(route('admin.schools.index'));

        $this->assertDatabaseHas('schools', [
            'name' => 'Al-Nahda School',
            'address' => 'Main Street',
        ]);
    }

    public function test_admin_can_view_school_setup_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.schools.index'))
            ->assertOk()
            ->assertSee('Create School');
    }
}
