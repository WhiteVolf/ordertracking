<?php

namespace Tests\Unit;

use App\Models\Nationality;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function profile_belongs_to_user_and_nationality()
    {
        $nationality = Nationality::factory()->create();
        $user = User::factory()->create();

        $profile = UserProfile::factory()->create([
            'user_id' => $user->id,
            'nationality_id' => $nationality->id,
        ]);

        $this->assertInstanceOf(User::class, $profile->user);
        $this->assertInstanceOf(Nationality::class, $profile->nationality);
    }
}

