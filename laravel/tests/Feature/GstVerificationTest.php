<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GstVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_gstin_verification_endpoint_validates_input(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($user)->postJson('/verify-gstin', [
            'gstin' => 'INVALID_GST',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gstin']);
    }
}
