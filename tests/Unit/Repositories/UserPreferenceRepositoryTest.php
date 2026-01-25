<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Models\UserPreference;
use App\Repositories\UserPreferenceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UserPreferenceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new UserPreferenceRepository(new UserPreference());
    }

    public function test_can_find_preference_by_user_id(): void
    {
        $user = User::factory()->create();
        $preference = UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi'],
            'preferred_categories' => ['technology'],
            'preferred_authors' => ['1'],
        ]);

        $result = $this->repository->findByUserId($user->id);

        $this->assertNotNull($result);
        $this->assertEquals($preference->id, $result->id);
    }

    public function test_returns_null_when_preference_not_found(): void
    {
        $result = $this->repository->findByUserId(999);

        $this->assertNull($result);
    }

    public function test_can_update_or_create_new_preference(): void
    {
        $user = User::factory()->create();

        $data = [
            'preferred_sources' => ['newsapi'],
            'preferred_categories' => ['technology'],
            'preferred_authors' => ['1'],
        ];

        $preference = $this->repository->updateOrCreatePreferences($user->id, $data);

        $this->assertNotNull($preference);
        $this->assertEquals($user->id, $preference->user_id);
        $this->assertEquals(['newsapi'], $preference->preferred_sources);
    }

    public function test_can_update_existing_preference(): void
    {
        $user = User::factory()->create();

        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['guardian'],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);

        $data = [
            'preferred_sources' => ['newsapi'],
            'preferred_categories' => ['technology'],
            'preferred_authors' => ['1'],
        ];

        $preference = $this->repository->updateOrCreatePreferences($user->id, $data);

        $this->assertEquals(1, UserPreference::count());
        $this->assertEquals(['newsapi'], $preference->preferred_sources);
        $this->assertEquals(['technology'], $preference->preferred_categories);
        $this->assertEquals(['1'], $preference->preferred_authors);
    }

    public function test_update_or_create_handles_empty_arrays(): void
    {
        $user = User::factory()->create();

        $data = [
            'preferred_sources' => [],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ];

        $preference = $this->repository->updateOrCreatePreferences($user->id, $data);

        $this->assertEmpty($preference->preferred_sources);
        $this->assertEmpty($preference->preferred_categories);
        $this->assertEmpty($preference->preferred_authors);
    }

    public function test_multiple_users_can_have_preferences(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->repository->updateOrCreatePreferences($user1->id, [
            'preferred_sources' => ['newsapi'],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);

        $this->repository->updateOrCreatePreferences($user2->id, [
            'preferred_sources' => ['guardian'],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);

        $pref1 = $this->repository->findByUserId($user1->id);
        $pref2 = $this->repository->findByUserId($user2->id);

        $this->assertEquals(['newsapi'], $pref1->preferred_sources);
        $this->assertEquals(['guardian'], $pref2->preferred_sources);
    }

    public function test_preferences_are_stored_as_json(): void
    {
        $user = User::factory()->create();

        $data = [
            'preferred_sources' => ['newsapi', 'guardian'],
            'preferred_categories' => ['technology', 'business'],
            'preferred_authors' => ['1', '2', '3'],
        ];

        $preference = $this->repository->updateOrCreatePreferences($user->id, $data);
        $preference->refresh();

        $this->assertIsArray($preference->preferred_sources);
        $this->assertIsArray($preference->preferred_categories);
        $this->assertIsArray($preference->preferred_authors);
    }

    public function test_can_clear_all_preferences(): void
    {
        $user = User::factory()->create();

        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi'],
            'preferred_categories' => ['technology'],
            'preferred_authors' => ['1'],
        ]);

        $data = [
            'preferred_sources' => [],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ];

        $preference = $this->repository->updateOrCreatePreferences($user->id, $data);

        $this->assertEmpty($preference->preferred_sources);
        $this->assertEmpty($preference->preferred_categories);
        $this->assertEmpty($preference->preferred_authors);
    }
}
