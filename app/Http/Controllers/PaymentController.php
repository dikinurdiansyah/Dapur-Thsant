<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('order')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $payment->load('order');
        return view('admin.payments.show', compact('payment'));
    }

    public function create(Order $order)
    {
        if ($order->payment) {
            return redirect()->route('admin.payments.show', $order->payment)
                ->with('info', 'Order ini sudah memiliki pembayaran.');
        }

        return view('admin.payments.create', compact('order'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id|unique:payments,order_id',
            'amount_paid' => 'required|numeric|min:0',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:2048|dimensions:max_width=4000,max_height=4000',
        ]);

        $path = $request->file('receipt_image')->store('receipts', 'local');

        Payment::create([
            'order_id' => $validated['order_id'],
            'amount_paid' => $validated['amount_paid'],
            'receipt_image_path' => $path,
            'payment_status' => 'PENDING_REVIEW',
        ]);

        return redirect()->route('admin.payments')
            ->with('success', 'Bukti pembayaran berhasil diupload.');
    }

    public function edit(Payment $payment)
    {
        $payment->load('order');
        return view('admin.payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048|dimensions:max_width=4000,max_height=4000',
            'payment_status' => 'required|in:PENDING_REVIEW,VERIFIED,REJECTED',
            'admin_notes' => 'nullable|string',
        ]);

        // Status final tidak boleh diubah lagi agar stok & status order tidak dihitung ganda.
        if ($payment->payment_status !== 'PENDING_REVIEW' && $validated['payment_status'] !== $payment->payment_status) {
            return back()->with('error', 'Status pembayaran yang sudah final tidak dapat diubah.');
        }

        if ($validated['payment_status'] === 'VERIFIED' && $payment->order?->status === 'DIBATALKAN') {
            return back()->with('error', 'Order sudah dibatalkan, pembayaran tidak bisa diverifikasi.');
        }

        if ($request->hasFile('receipt_image')) {
            if ($payment->receipt_image_path) {
                Storage::disk('local')->delete($payment->receipt_image_path);
            }
            $validated['receipt_image_path'] = $request->file('receipt_image')->store('receipts', 'local');
        }

        // Logic VERIFIED
        if ($validated['payment_status'] === 'VERIFIED' && $payment->payment_status !== 'VERIFIED') {
            $validated['verified_at'] = now();
            if ($payment->order) {
                $payment->order->update(['status' => 'SELESAI']); // ✅ Diubah jadi SELESAI
            }
        }

        // Logic REJECTED
        if ($validated['payment_status'] === 'REJECTED' && $payment->payment_status !== 'REJECTED') {
            $validated['verified_at'] = null;

            if ($payment->order) {
                // Kembalikan stok
                foreach ($payment->order->orderItems as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }

                $payment->order->update([
                    'status' => 'DIBATALKAN',
                    'rejection_reason' => $validated['admin_notes'] ?? 'Pembayaran ditolak',
                ]);
            }
        }

        $payment->update($validated);

        return redirect()->route('admin.payments')
            ->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->payment_status === 'VERIFIED') {
            return redirect()->route('admin.payments')
                ->with('error', 'Pembayaran yang sudah terverifikasi tidak boleh dihapus agar riwayat tetap utuh.');
        }

        $order = $payment->order;

        if ($payment->receipt_image_path) {
            Storage::disk('local')->delete($payment->receipt_image_path);
        }

        $payment->delete();

        // Kembalikan order ke status awal karena bukti bayarnya dihapus.
        if ($order && $order->status === 'DIPROSES') {
            $order->update(['status' => 'MENUNGGU_KONFIRMASI']);
        }

        return redirect()->route('admin.payments')
            ->with('success', 'Pembayaran berhasil dihapus.');
    }

    // ==========================================
    // CUSTOMER: Upload Bukti Bayar (Tanpa Login)
    // ✅ DIPERBAIKI: Setelah upload, status order otomatis jadi DIPROSES
    // ==========================================
    public function storeGuest(Request $request)
    {
        // ✅ Pesanan diambil dari sesi (hasil checkout), bukan dari order_id kiriman form,
        // sehingga pelanggan tidak bisa menempelkan bukti bayar ke pesanan orang lain.
        $order = Order::find(session('last_order_id'));

        if (! $order) {
            return back()->with('error', 'Sesi pesanan tidak ditemukan. Silakan buka kembali halaman konfirmasi pesanan Anda.');
        }

        if ($order->payment_method !== 'TRANSFER_BANK') {
            return back()->with('error', 'Unggah bukti transfer hanya berlaku untuk pembayaran transfer bank.');
        }

        if ($order->status === 'DIBATALKAN') {
            return back()->with('error', 'Pesanan ini sudah dibatalkan dan tidak bisa diunggah bukti bayarnya.');
        }

        $request->validate([
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:2048|dimensions:max_width=4000,max_height=4000',
        ]);

        if ($order->payment) {
            return back()->with('error', 'Order ini sudah memiliki bukti pembayaran. Silakan cek halaman Status.');
        }

        $path = $request->file('receipt_image')->store('receipts', 'local');

        Payment::create([
            'order_id' => $order->id,
            'amount_paid' => $order->total_amount, // ✅ nominal ditentukan server, bukan dari form
            'receipt_image_path' => $path,
            'payment_status' => 'PENDING_REVIEW',
        ]);

        // ✅ OTOMATIS UBAH STATUS ORDER MENJADI DIPROSES SETELAH PELANGGAN UPLOAD
        $order->update(['status' => 'DIPROSES']);

        return redirect()->route('orders.status.show', ['order' => $order->order_ref])
            ->with('success', 'Bukti pembayaran berhasil diupload! Pesanan Anda sedang diproses.');
    }

    // ==========================================
    // ADMIN: Verifikasi Cepat dari Detail Order
    // ✅ DIPERBAIKI: Setelah admin verifikasi, status order jadi SELESAI
    // ==========================================
    public function verify(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:VERIFIED,REJECTED',
            'rejection_note' => 'nullable|string',
        ]);

        if ($payment->payment_status !== 'PENDING_REVIEW') {
            return back()->with('error', 'Pembayaran ini sudah diproses sebelumnya.');
        }

        if ($validated['status'] === 'VERIFIED' && $payment->order?->status === 'DIBATALKAN') {
            return back()->with('error', 'Order sudah dibatalkan, pembayaran tidak bisa diverifikasi.');
        }

        if ($validated['status'] === 'VERIFIED') {
            $payment->update([
                'payment_status' => 'VERIFIED',
                'verified_at' => now(),
            ]);

            if ($payment->order) {
                // ✅ OTOMATIS UBAH STATUS ORDER MENJADI SELESAI SAAT ADMIN VERIFIKASI
                $payment->order->update(['status' => 'SELESAI']);
            }

            return back()->with('success', '✅ Pembayaran diverifikasi & Order ditandai SELESAI.');
        }

        if ($validated['status'] === 'REJECTED') {
            $payment->update([
                'payment_status' => 'REJECTED',
                'admin_notes' => $validated['rejection_note'] ?? 'Pembayaran ditolak',
                'verified_at' => null,
            ]);

            if ($payment->order) {
                // Kembalikan stok produk
                foreach ($payment->order->orderItems as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }

                $payment->order->update([
                    'status' => 'DIBATALKAN',
                    'rejection_reason' => $validated['rejection_note'] ?? 'Pembayaran ditolak',
                ]);
            }

            return back()->with('success', '❌ Pembayaran ditolak dan stok telah dikembalikan.');
        }

        return back()->with('error', 'Status tidak valid.');
    }

    // ==========================================
    // ADMIN: Tampilkan bukti bayar (file privat)
    // ==========================================
    public function receipt(Payment $payment)
    {
        if (! $payment->receipt_image_path || ! Storage::disk('local')->exists($payment->receipt_image_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($payment->receipt_image_path);
    }
}
