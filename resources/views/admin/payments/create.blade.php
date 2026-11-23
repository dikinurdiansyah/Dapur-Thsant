@extends('layouts.admin')

@section('title', 'Upload Bukti Pembayaran')

@section('content')
<div class="mb-4">
    <div class="font-mono" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">
        ■ MODULE // PAYMENT_ENTRY :: [ INPUT MANUAL BUKTI BAYAR ]
    </div>
    <h1 class="fw-bold mb-1" style="font-size: 1.8rem; color: var(--brown-dark);">
        UPLOAD BUKTI PEMBAYARAN
    </h1>
    <p class="text-muted mb-0">
        Order <strong class="font-mono">{{ $order->order_ref }}</strong> —
        {{ $order->customer_name }} ({{ $order->customer_phone }})
    </p>
</div>

<div class="admin-card">
    <div class="card-header-custom">
        <div class="card-title-custom"><i class="bi bi-receipt"></i> DATA ORDER</div>
        <span class="badge-admin" style="background: var(--cream); color: var(--brown-dark);">
            {{ $order->status }}
        </span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-label">SUBTOTAL</div>
            <div class="fw-bold font-mono">Rp {{ number_format($order->subtotal_amount, 0, ',', '.') }}</div>
        </div>
        <div class="col-md-4">
            <div class="kpi-label">DISKON</div>
            <div class="fw-bold font-mono">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</div>
        </div>
        <div class="col-md-4">
            <div class="kpi-label">TOTAL TAGIHAN</div>
            <div class="fw-bold font-mono" style="font-size: 1.2rem; color: var(--brown-dark);">
                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.payments.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">JUMLAH DIBAYAR (Rp) *</label>
                <input type="number" name="amount_paid" min="0" step="1"
                       class="form-control @error('amount_paid') is-invalid @enderror"
                       value="{{ old('amount_paid', (int) $order->total_amount) }}" required>
                @error('amount_paid') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">FILE BUKTI TRANSFER *</label>
                <input type="file" name="receipt_image" accept="image/jpeg,image/png,image/jpg"
                       class="form-control @error('receipt_image') is-invalid @enderror" required>
                @error('receipt_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted" style="font-size: 0.75rem;">JPG / PNG, maksimal 2MB.</small>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <a href="{{ route('admin.payments') }}" class="btn-admin-outline">[ BATAL ]</a>
            <button type="submit" class="btn-admin">
                <i class="bi bi-cloud-upload"></i> [ SIMPAN BUKTI PEMBAYARAN ]
            </button>
        </div>
    </form>
</div>
@endsection
