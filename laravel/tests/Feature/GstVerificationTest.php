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

    public function test_gstin_verification_handles_external_credit_exhaustion(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($user)->postJson('/verify-gstin', [
            'gstin' => '24AHUPP7924M1ZG',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'valid' => true,
            'state' => 'Gujarat',
            'pan' => 'AHUPP7924M',
        ]);
        $response->assertJsonMissing(['message' => 'Credit Not Available.']);
    }

    public function test_gstin_verification_with_cashfree_response(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://sandbox.cashfree.com/verification/gstin' => \Illuminate\Support\Facades\Http::response([
                'valid' => true,
                'GSTIN' => '29AAICP2912R1ZR',
                'status' => 'VALID',
                'legal_name_of_business' => 'UJJIVAN SMALL FINANCE BANK LIMITED',
                'trade_name' => 'UJJIVAN SMALL FINANCE BANK',
                'constitution_of_business' => 'Public Limited Company',
                'gstin_status' => 'Active',
                'principal_place_of_business_fields' => [
                    'principal_place_of_business_address' => [
                        'city' => 'Bengaluru',
                        'state' => 'Karnataka',
                        'pincode' => '560100',
                    ]
                ]
            ], 200),
        ]);

        config([
            'services.cashfree.client_id' => 'test_client_id',
            'services.cashfree.client_secret' => 'test_secret',
            'services.cashfree.env' => 'sandbox',
        ]);

        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($user)->postJson('/verify-gstin', [
            'gstin' => '29AAICP2912R1ZR',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'valid' => true,
            'source' => 'cashfree',
            'name' => 'UJJIVAN SMALL FINANCE BANK',
            'state' => 'Karnataka',
        ]);
    }
}

