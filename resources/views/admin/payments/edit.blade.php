@extends('layouts.admin')

@section('title', 'Edit Pembayaran')

@section('content')
<div class="mb-4">
    <div class="font-mono" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">
        ■ MODULE // PAYMENT_VERIFICATION :: [ EDIT DATA PEMBAYARAN ]
    </div>
    <h1 class="fw-bold mb-1" style="font-size: 1.8rem; color: var(--brown-dark);">
        EDIT PEMBAYARAN
    </h1>
    <p class="text-muted mb-0 font-mono" style="font-size: 0.85rem;">
        PAY-{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}
        @if($payment->order)
            • ORDER {{ $payment->order->order_ref }}
        @endif
    </p>
</div>

<div class="admin-card">
    <div class="card-header-custom">
        <div class="card-title-custom"><i class="bi bi-pencil-square"></i> FORM EDIT</div>
        <a href="{{ route('admin.payments.show', $payment) }}" class="btn-admin-outline btn-sm">
            <i class="bi bi-eye"></i> LIHAT DETAIL
        </a>
    </div>

    @if($payment->receipt_image_path)
        <div class="mb-4">
            <div class="kpi-label mb-2">BUKTI SAAT INI</div>
            <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank">
                <img src="{{ route('admin.payments.receipt', $payment) }}"
                     alt="Bukti Transfer" class="rounded shadow-sm"
                     style="max-height: 200px; border: 1px solid var(--border-color);">
            </a>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.payments.update', $payment) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">JUMLAH DIBAYAR (Rp) *</label>
                <input type="number" name="amount_paid" min="0" step="1"
                       class="form-control @error('amount_paid') is-invalid @enderror"
                       value="{{ old('amount_paid', (int) $payment->amount_paid) }}" required>
                @error('amount_paid') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">STATUS PEMBAYARAN *</label>
                <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                    <option value="PENDING_REVIEW" {{ old('payment_status', $payment->payment_status) === 'PENDING_REVIEW' ? 'selected' : '' }}>
                        PENDING_REVIEW (Menunggu Review)
                    </option>
                    <option value="VERIFIED" {{ old('payment_status', $payment->payment_status) === 'VERIFIED' ? 'selected' : '' }}>
                        VERIFIED (Terverifikasi)
                    </option>
                    <option value="REJECTED" {{ old('payment_status', $payment->payment_status) === 'REJECTED' ? 'selected' : '' }}>
                        REJECTED (Ditolak)
                    </option>
                </select>
                @error('payment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">GANTI BUKTI (OPSIONAL)</label>
                <input type="file" name="receipt_image" accept="image/jpeg,image/png,image/jpg"
                       class="form-control @error('receipt_image') is-invalid @enderror">
                @error('receipt_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">CATATAN ADMIN</label>
                <textarea name="admin_notes" rows="3"
                          class="form-control @error('admin_notes') is-invalid @enderror"
                          placeholder="Catatan verifikasi, mis. nominal kurang Rp 5.000">{{ old('admin_notes', $payment->admin_notes) }}</textarea>
                @error('admin_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="alert alert-warning mt-4 mb-0" style="font-size: 0.85rem;">
            <i class="bi bi-exclamation-triangle"></i>
            Mengubah status menjadi <strong>VERIFIED</strong> akan menandai order menjadi SELESAI.
            Status <strong>REJECTED</strong> akan membatalkan order dan mengembalikan stok.
        </div>

        <div class="d-flex gap-2 mt-4">
            <a href="{{ route('admin.payments') }}" class="btn-admin-outline">[ BATAL ]</a>
            <button type="submit" class="btn-admin">
                <i class="bi bi-save"></i> [ SIMPAN PERUBAHAN ]
            </button>
        </div>
    </form>
</div>
@endsection
