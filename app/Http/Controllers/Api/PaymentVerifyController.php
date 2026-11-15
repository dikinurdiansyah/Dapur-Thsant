<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PaymentVerifyController extends Controller
{
    public function __invoke(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:VERIFIED,REJECTED',
            'rejection_note' => 'nullable|string',
        ]);

        if ($payment->payment_status !== 'PENDING_REVIEW') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembayaran ini sudah diproses sebelumnya.',
            ], 409);
        }

        if ($validated['status'] === 'VERIFIED' && $payment->order?->status === 'DIBATALKAN') {
            return response()->json([
                'status' => 'error',
                'message' => 'Order sudah dibatalkan, pembayaran tidak bisa diverifikasi.',
            ], 409);
        }

        // Klik ganda / permintaan bersamaan: RuntimeException dari model diubah
        // menjadi 409 JSON, bukan 500.
        try {
            if ($validated['status'] === 'VERIFIED') {
                $payment->markVerified();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pembayaran diverifikasi dan order ditandai SELESAI.',
                    'data' => $payment->fresh('order'),
                ]);
            }

            $payment->markRejected($validated['rejection_note'] ?? 'Pembayaran ditolak');
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 409);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran ditolak dan stok telah dikembalikan.',
            'data' => $payment->fresh('order'),
        ]);
    }
}
