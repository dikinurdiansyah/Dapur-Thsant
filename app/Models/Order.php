<?php

namespace App\Models;

use App\Exceptions\StockException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_ref',
        'customer_name',
        'customer_phone',
        'shipping_address',
        'fulfillment_method',
        'promo_id',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_provider',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_ref)) {
                $order->order_ref = 'ORD-'.strtoupper(Str::random(10));
            }
        });
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class, 'promo_id', 'id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id', 'id');
    }

    public function calculateTotal(): void
    {
        $subtotal = $this->orderItems()->sum('subtotal');
        $this->subtotal_amount = $subtotal;

        $discount = 0;
        if ($this->promo) {
            $discount = $this->promo->calculateDiscount($subtotal);
        }
        $this->discount_amount = $discount;
        $this->total_amount = $subtotal - $discount;
    }

    /**
     * Kembalikan stok seluruh item pesanan ini.
     */
    public function restoreStock(): void
    {
        foreach ($this->orderItems()->get() as $item) {
            Product::whereKey($item->product_id)->increment('stock', $item->quantity);
        }
    }

    /**
     * Batalkan pesanan dan kembalikan stok TEPAT SATU KALI.
     *
     * Idempoten: pemanggilan kedua (misalnya admin membatalkan order lalu
     * menolak pembayarannya) tidak menambah stok lagi.
     *
     * @return bool true bila status berubah menjadi DIBATALKAN.
     */
    public function cancelWithReason(?string $reason = null): bool
    {
        if ($this->status === 'DIBATALKAN') {
            return false;
        }

        DB::transaction(function () use ($reason) {
            $order = static::lockForUpdate()->find($this->id);

            if (! $order || $order->status === 'DIBATALKAN') {
                return;
            }

            $order->restoreStock();

            $order->update([
                'status' => 'DIBATALKAN',
                'rejection_reason' => $reason ?? $order->rejection_reason,
            ]);

            $this->setRawAttributes($order->getAttributes(), true);
        });

        return $this->status === 'DIBATALKAN';
    }

    /**
     * Aktifkan kembali pesanan yang sudah dibatalkan, dengan memotong stok lagi
     * agar jumlah stok tetap konsisten dengan pesanan yang aktif.
     *
     * @throws StockException bila stok tidak mencukupi atau produk sudah dihapus.
     */
    public function reactivate(string $status): void
    {
        if ($this->status !== 'DIBATALKAN') {
            $this->update(['status' => $status]);

            return;
        }

        DB::transaction(function () use ($status) {
            $order = static::lockForUpdate()->find($this->id);

            if (! $order || $order->status !== 'DIBATALKAN') {
                throw new StockException('Status pesanan sudah berubah. Muat ulang halaman lalu coba lagi.');
            }

            $items = $order->orderItems()->get();

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);

                if (! $product) {
                    throw new StockException('Produk pada pesanan ini sudah dihapus, pesanan tidak bisa diaktifkan kembali.');
                }

                if ($product->stock < $item->quantity) {
                    throw new StockException("Stok {$product->name} tidak mencukupi untuk mengaktifkan kembali pesanan (butuh {$item->quantity}, tersedia {$product->stock}).");
                }
            }

            foreach ($items as $item) {
                Product::whereKey($item->product_id)->decrement('stock', $item->quantity);
            }

            $order->update([
                'status' => $status,
                'rejection_reason' => null,
            ]);

            $this->setRawAttributes($order->getAttributes(), true);
        });
    }
}
