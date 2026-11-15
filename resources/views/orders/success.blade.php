@extends('layouts.app')

@section('title', 'Konfirmasi Order - Berhasil')

@section('navbar')
<div class="top-navbar">
    <a href="{{ route('catalog.index') }}" class="back-btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="page-title">KONFIRMASI ORDER</span>
    <div class="user-icon">
        <i class="bi bi-person-fill"></i>
    </div>
</div>
@endsection

@section('content')
<div class="px-3 pt-4 pb-5">

    <!-- ✅ NOTIFIKASI SUKSES / ERROR (DITAMBAHKAN) -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-circle-fill"></i> Gagal mengupload:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <!-- AKHIR NOTIFIKASI -->

    <!-- Success Icon -->
    <div class="text-center mb-3">
        <div style="width: 80px; height: 80px; background: var(--green-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
            <i class="bi bi-check-lg" style="font-size: 2.5rem; color: var(--green-stock);"></i>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="text-center mb-3">
        <span class="badge-status d-inline-flex align-items-center gap-2">
            <span style="width: 8px; height: 8px; background: var(--green-stock); border-radius: 50%;"></span>
            STATUS: MASUK SISTEM
        </span>
    </div>

    <!-- Title -->
    <div class="text-center mb-4">
        <h2 class="fw-bold text-brown mb-2" style="font-size: 1.4rem;">PESANAN BERHASIL DITERIMA!</h2>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">
            Terima kasih, {{ $order->customer_name }}! Dapur Th Sant sedang menyiapkan pesanan Anda.
        </p>
    </div>

    <!-- Order Info Card -->
    <div class="card-warm p-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="font-mono fw-bold" style="font-size: 0.75rem; color: var(--text-muted); letter-spacing: 1px;">KODE PESANAN</span>
            <span class="badge-status">MENUNGGU KONFIRMASI</span>
        </div>

        <div class="card-warm p-3 mb-3" style="background: var(--cream-light);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="font-mono fw-bold text-brown" style="font-size: 1.1rem;">{{ $order->order_ref }}</div>
                    <small class="text-muted" style="font-size: 0.8rem;">
                        {{ $order->created_at->format('d F Y, H:i') }} WIB
                    </small>
                </div>
                <button type="button" class="btn-brown-outline px-3 py-2 d-flex align-items-center gap-2" style="font-size: 0.8rem; padding: 8px 12px;"
                        onclick="copyOrderCode()">
                    <i class="bi bi-clipboard"></i> SALIN
                </button>
            </div>
        </div>

        <div class="card-warm p-3 d-flex align-items-center gap-3">
            <i class="bi bi-clock text-brown" style="font-size: 1.3rem;"></i>
            <div>
                <div class="section-label mb-0">WAKTU PENYAJIAN</div>
                <div class="fw-bold text-brown">Estimasi Dikukus Hangat: 15 - 25 Menit</div>
            </div>
        </div>
    </div>

    <!-- Menu Details -->
    <div class="card-warm p-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-brown d-flex align-items-center gap-2" style="font-size: 1rem;">
                <i class="bi bi-receipt"></i> Rincian Menu
            </h5>
            <span class="badge-status">{{ $order->orderItems->count() }} Varian ({{ $order->orderItems->sum('quantity') }} Pcs)</span>
        </div>

        @foreach($order->orderItems as $item)
            <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom: 1px dashed var(--border-color);">
                <div style="width: 60px; height: 60px; flex-shrink: 0; background: var(--cream-dark); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    @if($item->product->image_url)
                        <img src="{{ asset('storage/' . $item->product->image_url) }}" class="w-100 h-100" style="object-fit: cover; border-radius: 8px;">
                    @else
                        <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">1:1</span>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold text-brown" style="font-size: 0.95rem;">{{ $item->product->name }}</div>
                    <small class="text-muted">{{ $item->product->description ? Str::limit($item->product->description, 30) : 'Isi 4 pcs' }}</small>
                    <div class="mt-1">
                        <span class="badge-stock" style="background: var(--cream-dark); color: var(--brown-dark); font-size: 0.7rem;">
                            {{ $item->quantity }}x Porsi
                        </span>
                    </div>
                </div>
                <div class="fw-bold text-brown font-mono" style="font-size: 0.95rem;">
                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                </div>
            </div>
        @endforeach

        <!-- Totals -->
        <div class="mt-3">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Subtotal Pesanan</span>
                <span class="font-mono text-brown">Rp {{ number_format($order->subtotal_amount, 0, ',', '.') }}</span>
            </div>

            @if($order->discount_amount > 0)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-success">Diskon Promo</span>
                    <span class="font-mono text-success">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Biaya Kemasan Higienis</span>
                <span class="font-mono text-brown">Rp 2.000</span>
            </div>

            @if($order->fulfillment_method === 'PICKUP')
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Biaya Pengambilan (Self-Pickup)</span>
                    <span class="font-mono text-success">Rp 0</span>
                </div>
            @else
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Ongkos Kirim Kurir</span>
                    <span class="font-mono text-brown">Rp 10.000</span>
                </div>
            @endif

            <hr class="divider">

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold text-brown" style="font-size: 0.9rem; letter-spacing: 1px;">TOTAL PEMBAYARAN</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Metode: {{ str_replace('_', ' ', $order->payment_method) }}</small>
                </div>
                <div class="fw-bold text-brown" style="font-size: 1.5rem;">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Shipping / Pickup Info -->
    <div class="card-warm p-4 mb-3">
        @if($order->fulfillment_method === 'PICKUP')
            <h5 class="fw-bold mb-3 text-brown d-flex align-items-center gap-2" style="font-size: 1rem;">
                <i class="bi bi-shop"></i> Lokasi Pengambilan
            </h5>
            <div class="mb-2">
                <div class="fw-bold text-brown">Dapur Th Sant - Outlet Ciumbuleuit</div>
                <div class="text-muted" style="font-size: 0.9rem;">Jl. Ciumbuleuit No. 88, Hegarmanah, Bandung</div>
            </div>
            <div class="mt-3">
                <div class="section-label">CATATAN PENGAMBILAN</div>
                <div class="card-warm p-3" style="background: var(--cream-light);">
                    <div class="fst-italic" style="font-size: 0.9rem; color: var(--text-dark);">
                        "{{ $order->shipping_address }}"
                    </div>
                </div>
            </div>
        @else
            <h5 class="fw-bold mb-3 text-brown d-flex align-items-center gap-2" style="font-size: 1rem;">
                <i class="bi bi-geo-alt-fill"></i> Tujuan Pengiriman
            </h5>
            <div class="mb-2">
                <div class="fw-bold text-brown">
                    {{ $order->customer_name }}
                    <span class="font-mono text-muted" style="font-size: 0.85rem; font-weight: 400;">{{ $order->customer_phone }}</span>
                </div>
                <div class="text-muted" style="font-size: 0.9rem;">{{ $order->shipping_address }}</div>
            </div>
        @endif
    </div>

    <!-- Payment & Upload Section -->
    @if($order->payment_method === 'TRANSFER_BANK' || $order->payment_method === 'QRIS')
        <div class="card-warm p-4 mb-3" style="background: #fff8e1; border-left: 4px solid var(--warning-sisa);">
            <h5 class="fw-bold mb-3 text-brown d-flex align-items-center gap-2" style="font-size: 1rem;">
                <i class="bi bi-bank"></i> Instruksi & Upload Pembayaran
            </h5>

            <div class="card-warm p-3 mb-3" style="background: white;">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">{{ $order->payment_provider ?? 'Rekening Tujuan' }}</span>
                    <span class="font-mono fw-bold text-brown">1234567890</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">a.n. Dapur Th Sant</span>
                    <button type="button" class="btn btn-link p-0 text-brown" style="font-size: 0.8rem;" onclick="alert('Nomor rekening disalin!')">Salin</button>
                </div>
                <div class="d-flex justify-content-between mt-2 pt-2" style="border-top: 1px dashed var(--border-color);">
                    <span class="text-muted fw-bold">Total Transfer</span>
                    <span class="font-mono fw-bold text-brown" style="font-size: 1.1rem;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- ✅ FORM UPLOAD BUKTI TRANSFER (PASTI BENAR) -->
            <form method="POST" action="{{ route('payments.guest.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="section-label">UPLOAD BUKTI TRANSFER <span class="text-danger">*</span></label>
                    <input type="file" name="receipt_image" class="form-control-warm w-100" accept="image/jpeg,image/png,image/jpg" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Format: JPG, PNG. Maksimal 2MB.</small>
                </div>

                <button type="submit" class="btn-brown w-100 d-flex justify-content-center align-items-center gap-2">
                    <i class="bi bi-upload"></i> KIRIM BUKTI PEMBAYARAN
                </button>
            </form>

            <small class="text-muted d-block text-center mt-3" style="font-size: 0.8rem;">
                <i class="bi bi-info-circle"></i> Setelah diupload, status akan otomatis berubah menjadi "DIPROSES".
            </small>
        </div>
    @else
        <div class="card-warm p-4 mb-3" style="background: var(--green-bg);">
            <h5 class="fw-bold mb-2 text-brown d-flex align-items-center gap-2" style="font-size: 1rem;">
                <i class="bi bi-cash-stack"></i> Pembayaran COD
            </h5>
            <p class="mb-0" style="font-size: 0.9rem;">
                Siapkan uang pas sebesar <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong> saat pesanan tiba.
            </p>
        </div>
    @endif

    <!-- Action Buttons -->
    <a href="{{ route('orders.status.show', ['order' => $order->order_ref]) }}" class="btn-brown w-100 d-flex justify-content-between align-items-center mb-2" style="padding: 16px 24px;">
        <span class="fw-bold">Lacak Status Pesanan</span>
        <i class="bi bi-arrow-right"></i>
    </a>

    <a href="https://wa.me/6281234567890?text=Halo%20Dapur%20Th%20Sant,%20saya%20mau%20konfirmasi%20order%20{{ $order->order_ref }}"
       class="btn-brown-outline w-100 d-flex justify-content-center align-items-center gap-2 mb-3" style="padding: 14px 24px;">
        <i class="bi bi-whatsapp" style="color: #25D366; font-size: 1.2rem;"></i>
        <span>Kirim Notifikasi via WhatsApp</span>
    </a>

    <div class="text-center">
        <a href="{{ route('catalog.index') }}" class="text-brown fw-bold" style="text-decoration: none; font-size: 0.9rem; letter-spacing: 1px; border-bottom: 2px solid var(--brown-dark);">
            KEMBALI KE KATALOG
        </a>
    </div>
</div>

@push('scripts')
<script>
    function copyOrderCode() {
        const code = '{{ $order->order_ref }}';
        navigator.clipboard.writeText(code).then(() => {
            alert('Kode order disalin: ' + code);
        }).catch(() => {
            const textArea = document.createElement("textarea");
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("copy");
            document.body.removeChild(textArea);
            alert('Kode order disalin: ' + code);
        });
    }
</script>
@endpush
@endsection
