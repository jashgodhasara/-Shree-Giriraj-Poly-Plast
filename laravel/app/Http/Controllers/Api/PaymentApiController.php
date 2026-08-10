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
                'payment_date'   => $p->payment_date->format('Y-m-d'),
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
            $payment = Payment::create($validated);

            // Update invoice payment status
            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $invoice->updatePaymentStatus();

            DB::commit();
            return response()->json([
                'success'       => true,
                'id'            => $payment->id,
                'invoice_status' => $invoice->status,
                'paid_amount'   => (float) $invoice->paid_amount,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Payment $payment)
    {
        $invoiceId = $payment->invoice_id;
        $payment->delete();

        // Recalculate invoice status after deleting payment
        $invoice = Invoice::find($invoiceId);
        if ($invoice) {
            $invoice->updatePaymentStatus();
        }

        return response()->json(['message' => 'Payment deleted.']);
    }
}
