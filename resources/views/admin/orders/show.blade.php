@extends('layouts.admin')

@section('title', 'Detail Order #' . $order->order_ref)

@section('content')

<style>
    .detail-header {
        background: linear-gradient(135deg, #3d2817 0%, #5c3d2e 100%);
        color: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }
</style>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb font-mono" style="font-size: 0.8rem;">
        <li class="breadcrumb-item"><a href="{{ route('admin.orders') }}" class="text-decoration-none">← KEMBALI KE DAFTAR ORDER</a></li>
        <li class="breadcrumb-item active">DETAIL PESANAN #{{ $order->order_ref }}</li>
    </ol>
</nav>

<!-- Order Header -->
<div class="detail-header">
    <div class="row">
        <div class="col-md-8">
            <h2 class="fw-bold mb-2">Pesanan #{{ $order->order_ref }}</h2>
            <div class="font-mono" style="font-size: 0.8rem; opacity: 0.9;">
                DIBUAT: {{ $order->created_at->format('d/m/Y H:i') }} WIB
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="p-3 rounded" style="background: rgba(255,255,255,0.1);">
                <div class="font-mono" style="font-size: 0.7rem; opacity: 0.8;">METODE PEMBAYARAN</div>
                <div class="fw-bold mb-2">{{ str_replace('_', ' ', $order->payment_method) }}</div>
                <span class="badge" style="background: {{ $order->payment?->payment_status === 'VERIFIED' ? 'var(--green-stock)' : '#e76f51' }}; color: white;">
                    {{ $order->payment?->payment_status ?? 'BELUM LUNAS' }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Form Update Status -->
<div class="admin-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">UPDATE STATUS PESANAN</h5>

        @if(session('success'))
            <div class="alert alert-success mb-0" style="font-size: 0.85rem;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-0" style="font-size: 0.85rem;">
                ️ {{ $errors->first() }}
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-6">
            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">STATUS SAAT INI</label>
            <div class="p-2 border rounded" style="background: var(--cream);">
                <strong>{{ str_replace('_', ' ', $order->status) }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">UBAH KE STATUS</label>
            <select name="status" class="form-select" required>
                <option value="MENUNGGU_KONFIRMASI" {{ $order->status == 'MENUNGGU_KONFIRMASI' ? 'selected' : '' }}>1. Menunggu Konfirmasi</option>
                <option value="DIPROSES" {{ $order->status == 'DIPROSES' ? 'selected' : '' }}>2. Sedang Diproses</option>
                <option value="DIKIRIM" {{ $order->status == 'DIKIRIM' ? 'selected' : '' }}>3. Dikirim</option>
                <option value="SELESAI" {{ $order->status == 'SELESAI' ? 'selected' : '' }}>4. Selesai</option>
                <option value="DIBATALKAN" {{ $order->status == 'DIBATALKAN' ? 'selected' : '' }}>X. Dibatalkan</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100" style="background: var(--brown-dark); border: none;">
                <i class="bi bi-save"></i> SIMPAN
            </button>
        </div>
    </form>
</div>

<!-- Info Pelanggan -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="admin-card">
            <div class="card-header-custom">
                <div class="card-title-custom"><i class="bi bi-person-fill"></i> Informasi Pelanggan</div>
            </div>
            <h4 class="fw-bold mb-2">{{ $order->customer_name }}</h4>
            <div class="mb-2">
                <i class="bi bi-telephone text-muted me-2"></i>
                <span>{{ $order->customer_phone }}</span>
            </div>
            <div class="mb-3">
                <i class="bi bi-envelope text-muted me-2"></i>
                <span>{{ $order->customer_email ?? 'N/A' }}</span>
            </div>
            <div>
                <div class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">ALAMAT PENGIRIMAN</div>
                <div class="mt-1">{{ $order->shipping_address }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="admin-card">
            <div class="card-header-custom">
                <div class="card-title-custom"><i class="bi bi-receipt"></i> Rincian Pembayaran</div>
            </div>
            <div class="mb-2 d-flex justify-content-between">
                <span>Subtotal</span>
                <span class="fw-bold">Rp {{ number_format($order->subtotal_amount, 0, ',', '.') }}</span>
            </div>
            <div class="mb-2 d-flex justify-content-between">
                <span>Ongkir</span>
                <span class="fw-bold">Rp 8.000</span>
            </div>
            @if($order->discount_amount > 0)
            <div class="mb-2 d-flex justify-content-between text-success">
                <span>Diskon</span>
                <span class="fw-bold">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                <div class="fw-bold" style="font-size: 1.3rem;">TOTAL</div>
                <div class="fw-bold" style="font-size: 1.5rem; color: var(--brown-dark);">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-4 font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
    BLUEPRINT: ADMIN_SPEC_v1.0 // WIREFRAME SCHEMATIC // DAPUR TH SANT
</div>
@endsection
