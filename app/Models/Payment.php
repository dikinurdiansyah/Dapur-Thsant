<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'amount_paid',
        'receipt_image_path',
        'payment_status',
        'admin_notes',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'verified_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Tandai pembayaran terverifikasi dan selesaikan pesanannya.
     */
    public function markVerified(): void
    {
        if ($this->payment_status !== 'PENDING_REVIEW') {
            throw new \RuntimeException('Pembayaran ini sudah diproses sebelumnya.');
        }

        if ($this->order?->status === 'DIBATALKAN') {
            throw new \RuntimeException('Order sudah dibatalkan, pembayaran tidak bisa diverifikasi.');
        }

        DB::transaction(function () {
            $this->update([
                'payment_status' => 'VERIFIED',
                'verified_at' => now(),
            ]);

            $this->order?->update(['status' => 'SELESAI']);
        });
    }

    /**
     * Tolak pembayaran dan batalkan pesanan (stok dikembalikan sekali saja
     * lewat Order::cancelWithReason()).
     */
    public function markRejected(?string $note = null): void
    {
        if ($this->payment_status !== 'PENDING_REVIEW') {
            throw new \RuntimeException('Pembayaran ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($note) {
            $this->update([
                'payment_status' => 'REJECTED',
                'admin_notes' => $note ?? $this->admin_notes ?? 'Pembayaran ditolak',
                'verified_at' => null,
            ]);

            $this->order?->cancelWithReason($note ?? 'Pembayaran ditolak');
        });
    }
}
