@extends('layouts.admin')

@section('title', 'Detail Pembayaran')

@section('content')
<div class="mb-4">
    <div class="font-mono" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">
        ■ MODULE // PAYMENT_VERIFICATION :: [ DETAIL BUKTI BAYAR ]
    </div>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem; color: var(--brown-dark);">
                DETAIL PEMBAYARAN
            </h1>
            <p class="text-muted mb-0 font-mono" style="font-size: 0.85rem;">
                PAY-{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}
                @if($payment->order)
                    • ORDER {{ $payment->order->order_ref }}
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.payments') }}" class="btn-admin-outline">
                <i class="bi bi-arrow-left"></i> [ KEMBALI ]
            </a>
            <a href="{{ route('admin.payments.edit', $payment) }}" class="btn-admin">
                <i class="bi bi-pencil"></i> [ EDIT ]
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="admin-card">
            <div class="card-header-custom">
                <div class="card-title-custom"><i class="bi bi-image"></i> BUKTI TRANSFER</div>
                @if($payment->payment_status === 'VERIFIED')
                    <span class="badge-admin badge-completed">■ Terverifikasi</span>
                @elseif($payment->payment_status === 'REJECTED')
                    <span class="badge-admin badge-cancelled">■ Ditolak</span>
                @else
                    <span class="badge-admin badge-pending">■ Menunggu Review</span>
                @endif
            </div>

            @if($payment->receipt_image_path)
                <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank">
                    <img src="{{ route('admin.payments.receipt', $payment) }}"
                         alt="Bukti Transfer" class="img-fluid rounded shadow-sm"
                         style="max-height: 480px; width: 100%; object-fit: contain; background: var(--cream);">
                </a>
                <div class="mt-3 text-center">
                    <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank"
                       class="btn-admin-outline btn-sm">
                        <i class="bi bi-box-arrow-up-right"></i> BUKA GAMBAR UKURAN PENUH
                    </a>
                </div>
            @else
                <p class="text-muted text-center py-4 mb-0">Tidak ada file bukti transfer.</p>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="admin-card">
            <div class="card-header-custom">
                <div class="card-title-custom"><i class="bi bi-info-circle"></i> RINGKASAN</div>
            </div>
            <table class="table table-sm mb-0">
                <tr>
                    <td class="text-muted">Jumlah dibayar</td>
                    <td class="text-end fw-bold font-mono">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Total order</td>
                    <td class="text-end fw-bold font-mono">
                        Rp {{ number_format($payment->order->total_amount ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Metode</td>
                    <td class="text-end fw-bold">
                        {{ $payment->order->payment_method ?? '-' }}
                        {{ $payment->order->payment_provider ? ' / ' . $payment->order->payment_provider : '' }}
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Waktu upload</td>
                    <td class="text-end font-mono" style="font-size: 0.85rem;">
                        {{ $payment->created_at?->format('d M Y H:i') ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Diverifikasi</td>
                    <td class="text-end font-mono" style="font-size: 0.85rem;">
                        {{ $payment->verified_at?->format('d M Y H:i') ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Status order</td>
                    <td class="text-end fw-bold">{{ $payment->order->status ?? '-' }}</td>
                </tr>
            </table>

            @if($payment->admin_notes)
                <div class="alert alert-warning mt-3 mb-0" style="font-size: 0.85rem;">
                    <strong>Catatan admin:</strong> {{ $payment->admin_notes }}
                </div>
            @endif
        </div>

        <div class="admin-card">
            <div class="card-header-custom">
                <div class="card-title-custom"><i class="bi bi-shield-check"></i> VERIFIKASI CEPAT</div>
            </div>

            @if($payment->order)
                <p class="text-muted" style="font-size: 0.85rem;">
                    Pelanggan: <strong>{{ $payment->order->customer_name }}</strong>
                    ({{ $payment->order->customer_phone }})
                </p>
                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                        @csrf
                        <input type="hidden" name="status" value="VERIFIED">
                        <button type="submit" class="btn-admin w-100"
                                onclick="return confirm('Verifikasi pembayaran ini? Order akan ditandai SELESAI.')">
                            <i class="bi bi-check-circle"></i> [ VERIFIKASI & SELESAIKAN ORDER ]
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                        @csrf
                        <input type="hidden" name="status" value="REJECTED">
                        <div class="mb-2">
                            <label class="form-label font-mono" style="font-size: 0.72rem; font-weight: 700;">
                                ALASAN PENOLAKAN
                            </label>
                            <input type="text" name="rejection_note" class="form-control"
                                   placeholder="Contoh: nominal tidak sesuai">
                        </div>
                        <button type="submit" class="btn w-100"
                                style="background: #fde8e8; color: #c53030; border: none; padding: 10px; border-radius: 8px; font-weight: 600;"
                                onclick="return confirm('Tolak pembayaran ini? Stok akan dikembalikan.')">
                            <i class="bi bi-x-circle"></i> [ TOLAK PEMBAYARAN ]
                        </button>
                    </form>
                </div>
            @else
                <p class="text-muted mb-0">Data order terkait tidak ditemukan.</p>
            @endif
        </div>
    </div>
</div>
@endsection
