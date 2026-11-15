@extends('layouts.admin')

@section('title', 'Kelola Kode Promo')
@section('page-title', 'Manajemen Kode Promo & Voucher Diskon')

@section('content')
<div class="mb-4">
    <div class="font-mono" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">
        ■ MODULE // PROMO_LOYALTY_MGR :: [ LIVE SINKRONISASI P4 ]
    </div>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem; color: var(--brown-dark);">
                MANAJEMEN KODE PROMO & VOUCHER DISKON
            </h1>
            <p class="text-muted mb-0">Kelola kode promo promosi yang langsung dapat digunakan pelanggan pada layar Checkout (P4)</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn-admin-outline" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i> [ SINKRONKAN POS ]
            </button>
            <button class="btn-admin" data-bs-toggle="modal" data-bs-target="#modalAddPromo">
                <i class="bi bi-plus"></i> [ + BUAT KODE PROMO BARU ]
            </button>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="kpi-label"><i class="bi bi-ticket-perforated"></i> TOTAL PROMO AKTIF</div>
                <span class="badge-admin badge-completed">LIVE</span>
            </div>
            <div class="kpi-value">{{ $promos->where('is_active', true)->count() }} Voucher</div>
            <div class="text-muted" style="font-size: 0.8rem;">■ Siap dipakai pada keranjang belanja</div>
            <div class="d-flex justify-content-between mt-3 font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
                <span>INDEX: VOUCHER_ACTIVE</span>
                <span>POOL: {{ $promos->where('is_active', true)->count() }}/{{ $promos->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="kpi-label"><i class="bi bi-graph-up"></i> TOTAL DIGUNAKAN</div>
                <span class="badge-admin" style="background: #dbeafe; color: #1e40af;">+18% WK</span>
            </div>
            <div class="kpi-value">142x</div>
            <div class="text-success" style="font-size: 0.8rem;">↗ +18% minggu ini (vs 120x)</div>
            <div class="d-flex justify-content-between mt-3 font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
                <span>CHECKOUT_CONV: 42.8%</span>
                <span>LOG: REALTIME</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="kpi-label"><i class="bi bi-cash-stack"></i> TOTAL DISKON</div>
                <span class="badge-admin" style="background: var(--cream); color: var(--brown-dark);">YTD 2026</span>
            </div>
            <div class="kpi-value">Rp 738.400</div>
            <div class="text-muted" style="font-size: 0.8rem;">■ Hemat dinikmati pelanggan</div>
            <div class="d-flex justify-content-between mt-3 font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
                <span>AVG_CLAIM: Rp 5.200</span>
                <span>ROI: EST +31% GMV</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: List Promo -->
    <div class="col-lg-7">
        <div class="admin-tabs mb-3">
            <a href="#" class="admin-tab active">[ SEMUA ({{ $promos->count() }}) ]</a>
            <a href="#" class="admin-tab">[ AKTIF ({{ $promos->where('is_active', true)->count() }}) ]</a>
            <a href="#" class="admin-tab">[ KEDALUWARSA ]</a>
            <a href="#" class="admin-tab">[ NONAKTIF ]</a>
            <div class="ms-auto">
                <input type="text" id="searchPromo" class="form-control form-control-sm" placeholder="🔍 Cari kode..." style="width: 200px;" onkeyup="filterPromos()">
            </div>
        </div>

        @foreach($promos as $promo)
        @php
            $validUntil = $promo->valid_until ? \Carbon\Carbon::parse($promo->valid_until) : null;
            $isExpired = $validUntil && $validUntil->isPast();
        @endphp
        <div class="admin-card mb-3 promo-card" data-code="{{ strtolower($promo->code) }}" style="{{ !$promo->is_active || $isExpired ? 'opacity: 0.6;' : '' }}">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="badge-admin" style="background: {{ $promo->discount_type === 'PERCENT' ? 'var(--warning-bg)' : 'var(--cream-dark)' }}; color: var(--brown-dark); font-size: 0.85rem; padding: 6px 10px;">
                        {{ $promo->discount_type === 'PERCENT' ? '% ' . $promo->discount_amount : 'Rp' . number_format($promo->discount_amount, 0, ',', '.') }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <strong class="font-mono" style="font-size: 1.1rem;">{{ $promo->code }}</strong>
                            @if($promo->is_active && !$isExpired)
                                <span class="badge-admin badge-completed">■ Aktif</span>
                            @elseif($isExpired)
                                <span class="badge-admin badge-cancelled">Kedaluwarsa</span>
                            @else
                                <span class="badge-admin" style="background: var(--cream-dark); color: var(--text-muted);">[TUTUP]</span>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size: 0.85rem;">
                            {{ $promo->discount_type === 'PERCENT' ? 'Diskon Persentase ' . $promo->discount_amount . '%' : 'Potongan Langsung Rp ' . number_format($promo->discount_amount, 0, ',', '.') }}
                            @if($promo->min_order_amount > 0)
                                • Min. Belanja Rp {{ number_format($promo->min_order_amount, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="font-mono" style="font-size: 0.75rem;">[{{ $promo->is_active ? 'ON' : 'OFF' }}]</span>
                    <form method="POST" action="{{ route('admin.promos.update', $promo) }}" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="code" value="{{ $promo->code }}">
                        <input type="hidden" name="discount_type" value="{{ $promo->discount_type }}">
                        <input type="hidden" name="discount_amount" value="{{ $promo->discount_amount }}">
                        <input type="hidden" name="min_order_amount" value="{{ $promo->min_order_amount }}">
                        <input type="hidden" name="valid_until" value="{{ $validUntil ? $validUntil->format('Y-m-d') : \Carbon\Carbon::now()->addDays(30)->format('Y-m-d') }}">
                        <input type="hidden" name="is_active" value="{{ $promo->is_active ? '0' : '1' }}">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" {{ $promo->is_active ? 'checked' : '' }} onchange="this.form.submit()" style="accent-color: var(--brown-dark);">
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <div class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">Min. Belanja: <strong>Rp {{ number_format($promo->min_order_amount, 0, ',', '.') }}</strong></div>
                </div>
                <div class="col-6 text-end">
                    <div class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">Periode: <strong>{{ $validUntil ? $validUntil->format('d M Y') : '-' }}</strong></div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                <div class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">ID: {{ $promo->id }}</div>
                <div class="d-flex gap-2">
                    <button class="btn-admin-outline btn-sm" style="padding: 4px 12px; font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalEditPromo{{ $promo->id }}">[ EDIT ]</button>
                    <form method="POST" action="{{ route('admin.promos.destroy', $promo) }}" onsubmit="return confirm('Hapus promo {{ $promo->code }}?')" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="background: #fde8e8; color: #c53030; border: none; padding: 4px 12px; font-size: 0.75rem; border-radius: 6px;">[ HAPUS ]</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Promo -->
        <div class="modal fade" id="modalEditPromo{{ $promo->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.promos.update', $promo) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header" style="background: var(--cream);">
                            <h5 class="modal-title fw-bold">[ EDIT PROMO: {{ $promo->code }} ]</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">KODE PROMO *</label>
                                    <input type="text" name="code" class="form-control" value="{{ $promo->code }}" required style="text-transform: uppercase;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">TIPE DISKON *</label>
                                    <select name="discount_type" class="form-select" required>
                                        <option value="PERCENT" {{ $promo->discount_type == 'PERCENT' ? 'selected' : '' }}>Persentase (%)</option>
                                        <option value="FIXED" {{ $promo->discount_type == 'FIXED' ? 'selected' : '' }}>Nominal (Rp)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">NILAI DISKON *</label>
                                    <input type="number" name="discount_amount" class="form-control" value="{{ $promo->discount_amount }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">MIN. PEMBELIAN *</label>
                                    <input type="number" name="min_order_amount" class="form-control" value="{{ $promo->min_order_amount }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">BERLAKU SAMPAI *</label>
                                    <input type="date" name="valid_until" class="form-control" value="{{ $validUntil ? $validUntil->format('Y-m-d') : '' }}" required>
                                </div>
                                <div class="col-12">
                                    <div class="form-check p-3 border rounded" style="background: var(--cream);">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $promo->is_active ? 'checked' : '' }} style="accent-color: var(--brown-dark);">
                                        <label class="form-check-label font-mono" style="font-size: 0.8rem;">AKTIFKAN PROMO</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">[ BATAL ]</button>
                            <button type="submit" class="btn-admin"><i class="bi bi-save"></i> [ SIMPAN PERUBAHAN ]</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Right Column: Form Create -->
    <div class="col-lg-5">
        <div class="admin-card">
            <div class="card-header-custom">
                <div class="card-title-custom"><i class="bi bi-file-earmark-plus"></i> FORM PEMBUATAN KODE PROMO</div>
                <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">[FORM_ID: #VCH-2026]</span>
            </div>

            <form method="POST" action="{{ route('admin.promos.store') }}" id="formCreatePromo">
                @csrf
                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">KODE PROMO (KUPON) *</label>
                    <div class="input-group">
                        <span class="input-group-text font-mono">#</span>
                        <input type="text" name="code" id="inputCode" class="form-control" placeholder="DIMSUMJUMAT" style="text-transform: uppercase;" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">TIPE DISKON *</label>
                    <div class="d-flex gap-2">
                        <label class="flex-grow-1 text-center p-2 border rounded discount-type-label active-type" style="cursor: pointer; background: var(--brown-dark); color: white; transition: all 0.2s;" onclick="selectDiscountType('PERCENT', this)">
                            <input type="radio" name="discount_type" value="PERCENT" checked class="d-none">
                            [% PERSENTASE]
                        </label>
                        <label class="flex-grow-1 text-center p-2 border rounded discount-type-label" style="cursor: pointer; transition: all 0.2s;" onclick="selectDiscountType('FIXED', this)">
                            <input type="radio" name="discount_type" value="FIXED" class="d-none">
                            [Rp NOMINAL]
                        </label>
                        <label class="flex-grow-1 text-center p-2 border rounded discount-type-label" style="cursor: pointer; transition: all 0.2s;" onclick="selectDiscountType('ONGKIR', this)">
                            <input type="radio" name="discount_type" value="ONGKIR" class="d-none">
                            [GRATIS ONGKIR]
                        </label>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">NILAI DISKON *</label>
                        <input type="number" name="discount_amount" id="inputDiscount" class="form-control" placeholder="15" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">MIN. PEMBELIAN</label>
                        <input type="number" name="min_order_amount" id="inputMin" class="form-control" placeholder="50000">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">KUOTA PEMAKAIAN</label>
                        <input type="number" name="quota" class="form-control" placeholder="100">
                    </div>
                    <div class="col-6">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">MAKS. KUOTA / USER</label>
                        <input type="number" name="max_per_user" class="form-control" placeholder="1" value="1">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">PERIODE BERLAKU *</label>
                    <div class="input-group">
                        <input type="date" name="valid_from" class="form-control" required>
                        <span class="input-group-text">-</span>
                        <input type="date" name="valid_until" id="inputDate" class="form-control" required>
                    </div>
                </div>

                <div class="form-check mb-3 p-3 border rounded" style="background: var(--cream);">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked style="accent-color: var(--brown-dark);">
                    <label class="form-check-label font-mono" style="font-size: 0.8rem;">
                        [X] AKTIFKAN LANGSUNG KE PELANGGAN <span class="badge-admin" style="background: var(--brown-dark); color: white; font-size: 0.65rem;">DEFAULT</span>
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn-admin-outline flex-grow-1" data-bs-dismiss="modal">[ BATAL ]</button>
                    <button type="submit" class="btn-admin flex-grow-2"><i class="bi bi-save"></i> [ SIMPAN & TERBITKAN ]</button>
                </div>
            </form>
        </div>

        <!-- Preview Voucher -->
        <div class="admin-card mt-3">
            <div class="card-header-custom">
                <div class="card-title-custom"><i class="bi bi-phone"></i> PREVIEW VOUCHER (P4)</div>
                <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">SIMULASI_VIEWPORT</span>
            </div>
            <div class="p-3 border rounded" style="border-style: dashed !important; background: var(--cream-light);">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="font-mono fw-bold" style="font-size: 0.9rem;" id="previewCode">[ DIMSUMJUMAT ]</div>
                        <span class="badge-admin badge-completed" style="font-size: 0.7rem;">[ Siap Klaim ]</span>
                    </div>
                    <button class="btn-admin btn-sm" type="button">PAKAI</button>
                </div>
                <div class="fw-bold" style="font-size: 1.1rem;" id="previewDiscount">Diskon 15%</div>
                <div class="text-muted" style="font-size: 0.8rem;" id="previewDetail">Min. blj Rp 50.000 • Berlaku hingga 31 Okt 2026</div>
                <div class="text-end font-mono" style="font-size: 0.7rem; color: var(--text-muted);">P4_READY</div>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-4 font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
    BLUEPRINT: ADMIN_SPEC_v1.0 // WIREFRAME SCHEMATIC // DAPUR TH SANT
</div>
@endsection

@push('scripts')
<script>
// 1. Fungsi agar tombol Tipe Diskon bisa diklik dan berubah warna
function selectDiscountType(type, element) {
    document.querySelectorAll('.discount-type-label').forEach(label => {
        label.style.background = '';
        label.style.color = '';
    });
    element.style.background = 'var(--brown-dark)';
    element.style.color = 'white';
    updatePreview();
}

// 2. Fungsi update preview secara real-time
function updatePreview() {
    const code = document.getElementById('inputCode').value || 'DIMSUMJUMAT';
    const discount = document.getElementById('inputDiscount').value || '15';
    const min = document.getElementById('inputMin').value || '50000';
    const dateVal = document.getElementById('inputDate').value;
    const type = document.querySelector('input[name="discount_type"]:checked').value;

    let discText = type === 'PERCENT' ? `Diskon ${discount}%` : (type === 'FIXED' ? `Diskon Rp ${parseInt(discount).toLocaleString('id-ID')}` : 'Gratis Ongkir');
    let minText = min ? `Min. blj Rp ${parseInt(min).toLocaleString('id-ID')}` : 'Tanpa min. blj';
    let dateText = dateVal ? new Date(dateVal).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) : '31 Okt 2026';

    document.getElementById('previewCode').textContent = `[ ${code.toUpperCase()} ]`;
    document.getElementById('previewDiscount').textContent = discText;
    document.getElementById('previewDetail').textContent = `${minText} • Berlaku hingga ${dateText}`;
}

// 3. Pasang event listener ke semua input
document.addEventListener('DOMContentLoaded', function() {
    const inputs = ['inputCode', 'inputDiscount', 'inputMin', 'inputDate'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if(el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        }
    });
    document.querySelectorAll('input[name="discount_type"]').forEach(radio => {
        radio.addEventListener('change', updatePreview);
    });
    updatePreview(); // Jalankan sekali di awal
});

// 4. Filter Pencarian
function filterPromos() {
    const input = document.getElementById('searchPromo').value.toLowerCase();
    document.querySelectorAll('.promo-card').forEach(card => {
        card.style.display = card.dataset.code.includes(input) ? '' : 'none';
    });
}
</script>
@endpush
