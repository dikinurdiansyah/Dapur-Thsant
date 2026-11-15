<?php

namespace App\Http\Controllers;

use App\Exceptions\StockException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    // ==========================================
    // ADMIN: Daftar Semua Order
    // ==========================================
    public function index()
    {
        // ✅ DIPERBAIKI: Menggunakan get() agar tampil semua tanpa pagination (panah besar hilang)
        $orders = Order::with(['promo', 'orderItems.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.orders.index', compact('orders'));
    }

    // ==========================================
    // ✅ TAMBAHAN BARU: ADMIN: Detail Order (Halaman Aktif)
    // ==========================================
    public function detail()
    {
        // Ambil order yang sedang diproses (aktif)
        $order = Order::with(['promo', 'orderItems.product', 'payment'])
            ->whereIn('status', ['DIPROSES', 'DIKIRIM'])
            ->latest()
            ->first();

        // Jika tidak ada yang aktif, ambil order terbaru
        if (!$order) {
            $order = Order::with(['promo', 'orderItems.product', 'payment'])
                ->latest()
                ->first();
        }

        if (!$order) {
            return view('admin.orders.detail', ['order' => null, 'currentStage' => 1]);
        }

        $stageMap = [
            'MENUNGGU_KONFIRMASI' => 1,
            'DIPROSES' => 2,
            'DIKIRIM' => 3,
            'SELESAI' => 5,
            'DIBATALKAN' => 0
        ];

        $currentStage = $stageMap[$order->status] ?? 1;

        return view('admin.orders.detail', compact('order', 'currentStage'));
    }

    // ==========================================
    // CUSTOMER: Tampilkan Form Checkout
    // ==========================================
    public function create()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('catalog.index')
                ->with('error', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
        }

        $products = Product::whereIn('id', array_keys($cart))->get();
        $promos = Promo::where('is_active', true)
            ->where('valid_until', '>=', now())
            ->get();

        $subtotal = 0;
        $cartItems = [];
        foreach ($cart as $productId => $item) {
            $product = $products->firstWhere('id', $productId);
            if ($product) {
                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                ];
            }
        }

        return view('orders.checkout', compact('cartItems', 'subtotal', 'promos'));
    }

    // ==========================================
    // CUSTOMER: Proses Checkout (Simpan Order)
    // ==========================================
    public function store(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Keranjang kosong.');
        }

        // Validasi Input (Termasuk payment_provider untuk BCA/GoPay/dll)
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'fulfillment_method' => 'required|in:DELIVERY,PICKUP',
            'delivery_address' => 'required_if:fulfillment_method,DELIVERY|string',
            'pickup_note' => 'required_if:fulfillment_method,PICKUP|string',
            'payment_method' => 'required|in:TRANSFER_BANK,COD',
            'payment_provider' => 'required_if:payment_method,TRANSFER_BANK|string|in:BCA,MANDIRI,GOPAY,OVO,SHOPEEPAY,QRIS',
            'promo_code' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Tentukan alamat final berdasarkan metode yang dipilih
            $finalAddress = ($validated['fulfillment_method'] === 'DELIVERY')
                ? $validated['delivery_address']
                : $validated['pickup_note'];

            // Cek Promo
            $promo = null;
            if (!empty($validated['promo_code'])) {
                $promo = Promo::where('code', strtoupper(trim($validated['promo_code'])))->first();
                if (!$promo || !$promo->isValid()) {
                    throw new \Exception('Kode promo tidak valid atau sudah kedaluwarsa.');
                }

                $subtotalTemp = 0;
                foreach ($cart as $productId => $item) {
                    $product = Product::find($productId);
                    if ($product) {
                        $subtotalTemp += $product->price * $item['quantity'];
                    }
                }
                if ($subtotalTemp < $promo->min_order_amount) {
                    throw new \Exception("Minimum order untuk promo ini adalah Rp " . number_format($promo->min_order_amount, 0, ',', '.'));
                }
            }

            // Buat Order
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $finalAddress,
                'fulfillment_method' => $validated['fulfillment_method'],
                'payment_method' => $validated['payment_method'],
                'payment_provider' => $validated['payment_provider'] ?? null,
                'promo_id' => $promo?->id,
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'status' => 'MENUNGGU_KONFIRMASI',
            ]);

            // Loop Cart Items & Kurangi Stok
            $subtotal = 0;
            foreach ($cart as $productId => $item) {
                $product = Product::lockForUpdate()->findOrFail($productId);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi. Tersedia: {$product->stock}");
                }

                if (!$product->is_active) {
                    throw new \Exception("Produk {$product->name} tidak aktif.");
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'price_at_purchase' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            // Hitung Total Akhir (Ongkir dinamis + Kemasan + Diskon)
            $shippingCost = ($validated['fulfillment_method'] === 'PICKUP') ? 0 : 10000;
            $packagingCost = 2000;
            $discount = $promo ? $promo->calculateDiscount($subtotal) : 0;
            $finalTotal = max(0, $subtotal + $packagingCost + $shippingCost - $discount);

            $order->update([
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $finalTotal,
            ]);

            // Kosongkan Keranjang & Simpan ID Order ke Session
            session()->forget('cart');
            session(['last_order_id' => $order->id]);

            DB::commit();

            return redirect()->route('orders.success', ['order' => $order->order_ref]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ==========================================
    // CUSTOMER & ADMIN: Lihat Detail Order (SMART ROUTING)
    // ==========================================
    public function show(Order $order)
    {
        $order->load(['promo', 'orderItems.product', 'payment']);

        $stageMap = [
            'MENUNGGU_KONFIRMASI' => 1,
            'DIPROSES' => 2,
            'DIKIRIM' => 3,
            'SELESAI' => 5,
            'DIBATALKAN' => 0
        ];

        $currentStage = $stageMap[$order->status] ?? 1;

        // Jika diakses dari panel admin, tampilkan view admin
        if (request()->routeIs('admin.orders.show')) {
            return view('admin.orders.show', compact('order', 'currentStage'));
        }

        // Jika diakses dari sisi pelanggan, tampilkan halaman status
        return view('orders.status', compact('order'));
    }

    // ==========================================
    // ADMIN: Update Status Order
    // ==========================================
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:MENUNGGU_KONFIRMASI,DIPROSES,DIKIRIM,SELESAI,DIBATALKAN',
            'rejection_reason' => 'nullable|string',
        ]);

        if ($validated['status'] === 'DIBATALKAN' && empty($validated['rejection_reason'])) {
            return back()->with('error', 'Alasan pembatalan harus diisi.');
        }

        if ($validated['status'] === 'DIBATALKAN') {
            if ($order->status === 'SELESAI') {
                return back()->with('error', 'Order yang sudah selesai tidak dapat dibatalkan.');
            }

            // Idempoten: stok hanya dikembalikan sekali walau pembayarannya ditolak juga.
            $order->cancelWithReason($validated['rejection_reason']);

            return back()->with('success', 'Status order berhasil diperbarui.');
        }

        if ($order->status === 'DIBATALKAN') {
            try {
                // Pesanan aktif kembali, jadi stok harus dipotong lagi.
                $order->reactivate($validated['status']);
            } catch (StockException $e) {
                return back()->with('error', $e->getMessage());
            }

            return back()->with('success', 'Status order berhasil diperbarui.');
        }

        $order->update($validated);

        return back()->with('success', 'Status order berhasil diperbarui.');
    }

    // ==========================================
    // ADMIN: Hapus Order
    // ==========================================
    public function destroy(Order $order)
    {
        // Riwayat transaksi yang sudah selesai/terverifikasi tidak boleh dihapus.
        if ($order->status === 'SELESAI' || $order->payment?->payment_status === 'VERIFIED') {
            return redirect()->route('admin.orders')
                ->with('error', 'Order yang sudah selesai/terverifikasi tidak boleh dihapus agar riwayat tetap utuh.');
        }

        // Kembalikan stok hanya jika order belum dibatalkan (menghindari stok dobel).
        if ($order->status !== 'DIBATALKAN') {
            $order->restoreStock();
        }

        // Bukti bayar ikut dihapus agar tidak tertinggal sebagai file yatim.
        if ($order->payment?->receipt_image_path) {
            Storage::disk('local')->delete($order->payment->receipt_image_path);
        }

        $order->delete();

        return redirect()->route('admin.orders')
            ->with('success', 'Order berhasil dihapus dan stok dikembalikan.');
    }
}
