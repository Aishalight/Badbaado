<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\Specialty;
use App\Models\User;
use Database\Seeders\SpecialtySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderCatalogFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_specialty_seeder_populates_the_clinical_taxonomy(): void
    {
        $this->seed(SpecialtySeeder::class);

        $this->assertGreaterThan(0, Specialty::count());
        $this->assertSame(
            Specialty::count(),
            Specialty::distinct()->count('slug'),
            'Seeded specialties must have unique slugs.'
        );
        $this->assertDatabaseHas('specialties', ['slug' => 'emergency-medicine', 'name' => 'Emergency Medicine']);
    }

    public function test_seeding_specialties_twice_does_not_duplicate_rows(): void
    {
        $this->seed(SpecialtySeeder::class);
        $first = Specialty::count();

        $this->seed(SpecialtySeeder::class);

        $this->assertSame($first, Specialty::count());
    }

    public function test_specialties_are_returned_in_sort_order(): void
    {
        $this->seed(SpecialtySeeder::class);

        $sortOrders = Specialty::ordered()->pluck('sort_order');

        $this->assertSame($sortOrders->sort()->values()->all(), $sortOrders->values()->all());
    }

    public function test_hospitals_scope_excludes_private_practices(): void
    {
        $hospital = Hospital::factory()->create();
        $practice = Hospital::factory()->practice()->create();

        $ids = Hospital::hospitals()->pluck('id');

        $this->assertTrue($ids->contains($hospital->getKey()));
        $this->assertFalse($ids->contains($practice->getKey()));
    }

    public function test_practices_scope_returns_only_private_practices(): void
    {
        $hospital = Hospital::factory()->create();
        $practice = Hospital::factory()->practice()->create();

        $ids = Hospital::practices()->pluck('id');

        $this->assertTrue($ids->contains($practice->getKey()));
        $this->assertFalse($ids->contains($hospital->getKey()));
    }

    public function test_hospital_factory_creates_a_hospital_kind_by_default(): void
    {
        $this->assertSame('hospital', Hospital::factory()->create()->kind);
    }

    public function test_practice_factory_state_marks_the_facility_as_a_practice(): void
    {
        $this->assertSame('practice', Hospital::factory()->practice()->create()->kind);
    }

    public function test_a_user_resolves_their_assigned_specialty(): void
    {
        $this->seed(SpecialtySeeder::class);
        $specialty = Specialty::where('slug', 'cardiology')->firstOrFail();

        $user = User::factory()->create(['specialty_id' => $specialty->getKey()]);

        $this->assertTrue($specialty->is($user->specialty));
    }

    public function test_a_user_may_exist_without_a_specialty(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->specialty);
    }
}
