<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->merge([
            'fulfillment_method' => $request->input('fulfillment_method', 'DELIVERY'),
        ]);

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'fulfillment_method' => 'required|in:DELIVERY,PICKUP',
            'shipping_address' => 'required_if:fulfillment_method,DELIVERY|string',
            'pickup_note' => 'required_if:fulfillment_method,PICKUP|string|max:255',
            'payment_method' => 'required|in:TRANSFER_BANK,COD',
            'payment_provider' => 'nullable|string|in:BCA,MANDIRI,GOPAY,OVO,SHOPEEPAY,QRIS',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $fulfillment = $validated['fulfillment_method'];
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (! $product || ! $product->is_active) {
                    throw new \Exception('Produk tidak tersedia atau sudah nonaktif.');
                }

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                }

                // Kurangi stok secara atomik di dalam transaksi.
                $product->decrement('stock', $item['quantity']);

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'price_at_purchase' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                ];
            }

            $shippingCost = $fulfillment === 'PICKUP' ? 0 : 10000;
            $packagingCost = 2000;
            $totalAmount = $subtotal + $packagingCost + $shippingCost;

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $fulfillment === 'PICKUP'
                    ? $validated['pickup_note']
                    : $validated['shipping_address'],
                'fulfillment_method' => $fulfillment,
                'subtotal_amount' => $subtotal,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_provider' => $validated['payment_method'] === 'TRANSFER_BANK'
                    ? ($validated['payment_provider'] ?? null)
                    : null,
                'status' => 'MENUNGGU_KONFIRMASI',
            ]);

            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat',
                'data' => $order->load('orderItems'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
