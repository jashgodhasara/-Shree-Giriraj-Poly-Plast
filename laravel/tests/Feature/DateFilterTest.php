<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Ledger;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_date_filter_today_and_custom_range(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer = Customer::create(['name' => 'Acme Corp', 'state' => 'Gujarat']);

        // Today's invoice
        $todayInvoice = Invoice::create([
            'invoice_number' => 'INV-TODAY',
            'customer_id'    => $customer->id,
            'invoice_date'   => Carbon::today()->toDateString(),
            'subtotal'       => 1000,
            'cgst'           => 90,
            'sgst'           => 90,
            'igst'           => 0,
            'grand_total'    => 1180,
            'paid_amount'    => 1180,
            'status'         => 'Paid',
        ]);

        // Last year's invoice
        $oldInvoice = Invoice::create([
            'invoice_number' => 'INV-OLD',
            'customer_id'    => $customer->id,
            'invoice_date'   => Carbon::today()->subYear()->toDateString(),
            'subtotal'       => 500,
            'cgst'           => 45,
            'sgst'           => 45,
            'igst'           => 0,
            'grand_total'    => 590,
            'paid_amount'    => 0,
            'status'         => 'Unpaid',
        ]);

        // Test filter preset=today
        $responseToday = $this->actingAs($user)->get('/invoices?preset=today');
        $responseToday->assertStatus(200);
        $responseToday->assertSee('INV-TODAY');
        $responseToday->assertDontSee('INV-OLD');

        // Test custom date range
        $customFrom = Carbon::today()->subYear()->startOfMonth()->toDateString();
        $customTo   = Carbon::today()->subYear()->endOfMonth()->toDateString();
        $responseCustom = $this->actingAs($user)->get("/invoices?preset=custom&date_from={$customFrom}&date_to={$customTo}");
        $responseCustom->assertStatus(200);
        $responseCustom->assertSee('INV-OLD');
        $responseCustom->assertDontSee('INV-TODAY');
    }

    public function test_ledger_date_filter(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        Ledger::create([
            'entity_type'      => 'Customer',
            'entity_id'        => 1,
            'transaction_date' => Carbon::today()->toDateString(),
            'type'             => 'Debit',
            'amount'           => 5000,
            'description'      => 'Today Entry',
        ]);

        Ledger::create([
            'entity_type'      => 'Customer',
            'entity_id'        => 1,
            'transaction_date' => Carbon::today()->subMonths(6)->toDateString(),
            'type'             => 'Credit',
            'amount'           => 3000,
            'description'      => 'Old Entry',
        ]);

        $res = $this->actingAs($user)->get('/ledger?preset=this_month');
        $res->assertStatus(200);
        $res->assertSee('Today Entry');
        $res->assertDontSee('Old Entry');
    }
}
