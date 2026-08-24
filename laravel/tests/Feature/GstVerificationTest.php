<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GstVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_gstin_verification_endpoint_validates_input_length(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($user)->postJson('/verify-gstin', [
            'gstin' => 'INVALID_GST',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gstin']);
    }

    public function test_gstin_verification_successfully_resolves_valid_gstin(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($user)->postJson('/verify-gstin', [
            'gstin' => '24AHUPP7924M1ZG',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'valid' => true,
            'gstin' => '24AHUPP7924M1ZG',
            'state' => 'Gujarat',
            'state_code' => '24',
            'pan' => 'AHUPP7924M',
        ]);
    }

    public function test_gstin_verification_resolves_maharashtra_gstin(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($user)->postJson('/verify-gstin', [
            'gstin' => '27AABCU9603R1ZN',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'valid' => true,
            'gstin' => '27AABCU9603R1ZN',
            'state' => 'Maharashtra',
            'state_code' => '27',
        ]);
    }
}

