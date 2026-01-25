<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAndUserPreferenceModelTest extends TestCase
{
    use RefreshDatabase;

    // User Tests
    public function test_user_has_fillable_attributes(): void
    {
        $user = new User();

        $fillable = ['name', 'email', 'password'];

        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_user_has_hidden_attributes(): void
    {
        $user = new User();

        $hidden = ['password', 'remember_token'];

        $this->assertEquals($hidden, $user->getHidden());
    }

    public function test_user_email_is_unique(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_has_one_preference(): void
    {
        $user = User::factory()->create();
        $preference = UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi'],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);

        $user->load('preference');

        $this->assertInstanceOf(UserPreference::class, $user->preference);
        $this->assertEquals($preference->id, $user->preference->id);
    }

    public function test_user_can_be_created_with_email_verification(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertNotNull($user->email_verified_at);
    }

    // UserPreference Tests
    public function test_user_preference_has_fillable_attributes(): void
    {
        $preference = new UserPreference();

        $fillable = ['user_id', 'preferred_sources', 'preferred_categories', 'preferred_authors'];

        $this->assertEquals($fillable, $preference->getFillable());
    }

    public function test_user_preference_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $preference = UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => [],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);

        $this->assertInstanceOf(User::class, $preference->user);
        $this->assertEquals($user->id, $preference->user->id);
    }

    public function test_user_preference_casts_preferences_to_array(): void
    {
        $user = User::factory()->create();
        $preference = UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi', 'guardian'],
            'preferred_categories' => ['technology'],
            'preferred_authors' => ['1', '2'],
        ]);

        $this->assertIsArray($preference->preferred_sources);
        $this->assertIsArray($preference->preferred_categories);
        $this->assertIsArray($preference->preferred_authors);
    }

    public function test_user_preference_stores_json_values(): void
    {
        $user = User::factory()->create();
        $preference = UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi', 'guardian'],
            'preferred_categories' => ['technology', 'business'],
            'preferred_authors' => ['1', '2', '3'],
        ]);

        $preference->refresh();

        $this->assertEquals(['newsapi', 'guardian'], $preference->preferred_sources);
        $this->assertEquals(['technology', 'business'], $preference->preferred_categories);
        $this->assertEquals(['1', '2', '3'], $preference->preferred_authors);
    }

    public function test_user_preference_can_have_empty_arrays(): void
    {
        $user = User::factory()->create();
        $preference = UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => [],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);

        $this->assertEmpty($preference->preferred_sources);
        $this->assertEmpty($preference->preferred_categories);
        $this->assertEmpty($preference->preferred_authors);
    }

    public function test_user_id_is_unique_in_preferences(): void
    {
        $user = User::factory()->create();

        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => [],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => [],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);
    }

    public function test_deleting_user_deletes_preference(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => [],
            'preferred_categories' => [],
            'preferred_authors' => [],
        ]);

        $this->assertEquals(1, UserPreference::count());

        $user->delete();

        $this->assertEquals(0, UserPreference::count());
    }

    public function test_user_can_create_tokens(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->assertNotNull($token->plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test-token',
        ]);
    }
}
