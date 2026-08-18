<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentApiController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with('invoice:id,invoice_number,customer_id,grand_total')
            ->latest()
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'invoice_id'     => $p->invoice_id,
                'invoice_number' => $p->invoice?->invoice_number,
                'amount'         => (float) $p->amount,
                'payment_date'   => $p->payment_date?->format('Y-m-d') ?? ($p->created_at?->format('Y-m-d') ?? now()->format('Y-m-d')),
                'payment_mode'   => $p->payment_mode,
                'reference_no'   => $p->reference_no,
                'remarks'        => $p->remarks,
            ]);

        return response()->json($payments);
    }

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
                    'error' => 'Payment amount exceeds pending balance of ₹' . number_format($pending, 2),
                ], 422);
            }

            $payment = Payment::create($validated);

            // Update invoice payment status
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
            return response()->json([
                'success'        => true,
                'id'             => $payment->id,
                'invoice_status' => $invoice->status,
                'paid_amount'    => (float) $invoice->paid_amount,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
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

            $invoiceId = $payment->invoice_id;
            $payment->delete();

            // Recalculate invoice status after deleting payment
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                $invoice->updatePaymentStatus();
            }

            DB::commit();
            return response()->json(['message' => 'Payment deleted and ledger updated.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
