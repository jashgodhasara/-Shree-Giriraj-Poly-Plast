<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test guest redirection to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');

        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
    }

    /**
     * Test authenticated user can access dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::where('email', 'admin@shreegiriraj.com')->first();
        if (!$user) {
            $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        }

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }
}

