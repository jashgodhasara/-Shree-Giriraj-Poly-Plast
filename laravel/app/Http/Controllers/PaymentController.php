<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id'   => 'required|exists:invoices,id',
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|in:Cash,Cheque,NEFT,RTGS,UPI,Other',
            'reference_no' => 'nullable|string|max:100',
            'remarks'      => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $pending = $invoice->pending_amount;

            if ($validated['amount'] > $pending + 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds pending balance of ₹' . number_format($pending, 2),
                ], 422);
            }

            Payment::create($validated);
            $invoice->updatePaymentStatus();

            // Auto-post Customer Credit entry to Ledger
            \App\Models\Ledger::create([
                'entity_type'      => 'Customer',
                'entity_id'        => $invoice->customer_id,
                'transaction_date' => $validated['payment_date'],
                'type'             => 'Credit',
                'amount'           => $validated['amount'],
                'description'      => 'Payment Received (' . $validated['payment_mode'] . ') for Invoice #' . $invoice->invoice_number,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment recorded successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Payment $payment)
    {
        DB::beginTransaction();
        try {
            $invoice = $payment->invoice;
            
            if ($invoice) {
                // Delete matching Customer Credit ledger entry
                \App\Models\Ledger::where('entity_type', 'Customer')
                    ->where('entity_id', $invoice->customer_id)
                    ->where('amount', $payment->amount)
                    ->where('description', 'LIKE', '%Invoice #' . $invoice->invoice_number . '%')
                    ->delete();
            }

            $payment->delete();
            
            if ($invoice) {
                $invoice->updatePaymentStatus();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment deleted and ledger updated.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
