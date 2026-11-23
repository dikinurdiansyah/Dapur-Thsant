@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="mb-4">
    <div class="font-mono" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">
        ■ MODULE // PAYMENT_VERIFICATION :: [ LIVE SINKRONISASI P5 ]
    </div>
    <h1 class="fw-bold mb-1" style="font-size: 1.8rem; color: var(--brown-dark);">
        VERIFIKASI PEMBAYARAN MASUK
    </h1>
    <p class="text-muted mb-0">Periksa bukti transfer pelanggan, lalu verifikasi atau tolak pembayaran.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-wallet2"></i> TOTAL PEMBAYARAN</div>
            <div class="kpi-value">{{ $payments->total() }}</div>
            <div class="text-muted" style="font-size: 0.8rem;">Seluruh bukti bayar yang tercatat</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-hourglass-split"></i> MENUNGGU REVIEW</div>
            <div class="kpi-value" style="color: #b45309;">
                {{ $payments->getCollection()->where('payment_status', 'PENDING_REVIEW')->count() }}
            </div>
            <div class="text-muted" style="font-size: 0.8rem;">Perlu diverifikasi admin</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-check-circle"></i> SUDAH DIVERIFIKASI</div>
            <div class="kpi-value" style="color: var(--green-stock);">
                {{ $payments->getCollection()->where('payment_status', 'VERIFIED')->count() }}
            </div>
            <div class="text-muted" style="font-size: 0.8rem;">Pembayaran sah & order selesai</div>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="card-header-custom">
        <div class="card-title-custom">
            <i class="bi bi-receipt"></i> DAFTAR BUKTI PEMBAYARAN
        </div>
        <span class="badge-admin" style="background: var(--cream); color: var(--brown-dark);">
            HALAMAN {{ $payments->currentPage() }} / {{ max($payments->lastPage(), 1) }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-admin table-hover align-middle">
            <thead>
                <tr>
                    <th>KODE ORDER</th>
                    <th>PELANGGAN</th>
                    <th>JUMLAH BAYAR</th>
                    <th>STATUS</th>
                    <th>WAKTU UPLOAD</th>
                    <th class="text-end">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>
                        <div class="fw-bold font-mono" style="font-size: 0.8rem;">
                            {{ $payment->order->order_ref ?? '-' }}
                        </div>
                        <small class="text-muted font-mono" style="font-size: 0.7rem;">
                            ID: PAY-{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}
                        </small>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $payment->order->customer_name ?? '-' }}</div>
                        <small class="text-muted">{{ $payment->order->customer_phone ?? '-' }}</small>
                    </td>
                    <td class="fw-bold font-mono">
                        Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}
                    </td>
                    <td>
                        @if($payment->payment_status === 'VERIFIED')
                            <span class="badge-admin badge-completed">■ Terverifikasi</span>
                        @elseif($payment->payment_status === 'REJECTED')
                            <span class="badge-admin badge-cancelled">■ Ditolak</span>
                        @else
                            <span class="badge-admin badge-pending">■ Menunggu Review</span>
                        @endif
                    </td>
                    <td class="font-mono" style="font-size: 0.8rem;">
                        {{ $payment->created_at?->format('d M Y H:i') ?? '-' }}
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.payments.show', $payment) }}" class="btn-admin-outline btn-sm"
                               style="padding: 4px 12px; font-size: 0.75rem;">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            <a href="{{ route('admin.payments.edit', $payment) }}" class="btn-admin-outline btn-sm"
                               style="padding: 4px 12px; font-size: 0.75rem;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}"
                                  onsubmit="return confirm('Hapus data pembayaran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm"
                                        style="background: #fde8e8; color: #c53030; border: none; padding: 4px 12px; font-size: 0.75rem; border-radius: 6px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2 mb-0">Belum ada bukti pembayaran yang diunggah.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $payments->links('pagination::bootstrap-5') }}
</div>
@endsection
