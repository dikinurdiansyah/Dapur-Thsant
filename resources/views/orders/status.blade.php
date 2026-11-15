@extends('layouts.app')

@section('title', 'Status Order - Tracking')

@section('navbar')
<div class="top-navbar">
    <div class="logo-section">
        <div class="logo-icon" style="background: var(--cream); border: 2px solid var(--brown-dark);">
            <i class="bi bi-circle-fill" style="font-size: 0.6rem; color: var(--brown-dark);"></i>
        </div>
        <span class="logo-text">STATUS ORDER</span>
    </div>
    <div class="user-icon">
        <i class="bi bi-person-fill"></i>
    </div>
</div>
@endsection

@section('content')
{{-- ✅ PENGECEKAN KEAMANAN: Jika Order Tidak Ditemukan --}}
@if(!$order)
    <div class="px-3 pt-5 text-center">
        <i class="bi bi-inbox" style="font-size: 4rem; color: var(--text-muted);"></i>
        <h4 class="mt-3 fw-bold text-brown">Pesanan Tidak Ditemukan</h4>
        <p class="text-muted mb-4">Silakan masukkan kode order yang valid atau buat pesanan baru.</p>

        <form action="{{ route('orders.status') }}" method="GET" class="d-flex gap-2 justify-content-center mb-3">
            <input type="text" name="ref" class="form-control-warm font-mono" placeholder="ORD-XXXXX" style="max-width: 250px; text-transform: uppercase;" required>
            <button type="submit" class="btn-brown px-4 font-mono fw-bold">[CEK]</button>
        </form>

        <a href="{{ route('catalog.index') }}" class="btn-brown-outline">
            <i class="bi bi-arrow-left"></i> Kembali ke Katalog
        </a>
    </div>

@else
{{-- ✅ Jika Order Ditemukan, Tampilkan Desain Lengkap --}}
@php
    $stageMap = [
        'MENUNGGU_KONFIRMASI' => 1,
        'DIPROSES' => 2,
        'DIKIRIM' => 3,
        'SELESAI' => 5,
        'DIBATALKAN' => 0
    ];

    $currentStage = $stageMap[$order->status] ?? 1;
    $totalStages = 5;
    $isCancelled = ($order->status === 'DIBATALKAN');
@endphp

<div class="px-3 pt-3 pb-5">
    <!-- Lacak Pesanan Mandiri -->
    <div class="card-warm p-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-brown font-mono" style="font-size: 0.85rem; letter-spacing: 1px;">LACAK PESANAN MANDIRI</h5>
            <span class="badge-stock d-flex align-items-center gap-1" style="background: var(--green-bg); color: var(--green-stock);">
                <span style="width: 6px; height: 6px; background: var(--green-stock); border-radius: 50%;"></span>
                LIVE TRACKING
            </span>
        </div>

        <form action="{{ route('orders.status') }}" method="GET">
            <div class="d-flex gap-2">
                <input type="text" name="ref" class="form-control-warm flex-grow-1 font-mono"
                       placeholder="ORD-DTS-YYYYMMDD-XXXX"
                       value="{{ $order->order_ref }}"
                       style="text-transform: uppercase;">
                <button type="submit" class="btn-brown px-4 font-mono fw-bold">[CEK]</button>
            </div>
        </form>
        <small class="text-muted font-mono mt-2 d-block" style="font-size: 0.7rem;">
            Format: ORD-DTS-YYYYMMDD-XXXX
        </small>
    </div>

    <!-- ID Referensi -->
    <div class="card-warm p-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="font-mono fw-bold" style="font-size: 0.75rem; color: var(--text-muted); letter-spacing: 1px;">ID REFERENSI</span>
            @if(!$isCancelled)
                <span class="badge-status">TAHAP {{ $currentStage }} DARI {{ $totalStages }}</span>
            @else
                <span class="badge-status" style="background: #fde8e8; color: #c53030;">DIBATALKAN</span>
            @endif
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <div class="font-mono fw-bold text-brown" style="font-size: 1.1rem;">#{{ $order->order_ref }}</div>
            <small class="text-muted font-mono" style="font-size: 0.8rem;">
                {{ $order->created_at->format('d M Y') }}
            </small>
        </div>
    </div>

    <!-- Status Banner Dinamis -->
    @php
        $bannerText = match($order->status) {
            'MENUNGGU_KONFIRMASI' => 'MENUNGGU KONFIRMASI KASIR / TAHAP 1',
            'DIPROSES' => 'SEDANG DIKUKUS FRESH / TAHAP 2',
            'DIKIRIM' => 'SEDANG DALAM PENGANTARAN / TAHAP 3',
            'SELESAI' => 'PESANAN TELAH SELESAI / TAHAP 5',
            'DIBATALKAN' => 'PESANAN DIBATALKAN',
            default => 'MEMPROSES PESANAN...'
        };
        $showSpinner = in_array($order->status, ['MENUNGGU_KONFIRMASI', 'DIPROSES', 'DIKIRIM']);
    @endphp

    <div class="card-warm p-3 mb-3 d-flex align-items-center gap-3" style="background: var(--cream); border-left: 4px solid {{ $isCancelled ? '#c53030' : 'var(--brown-dark)' }};">
        @if($showSpinner)
            <div class="spinner-border spinner-border-sm text-brown" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        @elseif($isCancelled)
            <i class="bi bi-x-circle-fill" style="font-size: 1.2rem; color: #c53030;"></i>
        @else
            <i class="bi bi-check-circle-fill" style="font-size: 1.2rem; color: var(--green-stock);"></i>
        @endif

        <div class="fw-bold text-brown" style="font-size: 0.9rem; letter-spacing: 0.5px;">
            {{ $bannerText }}
        </div>
    </div>

    <!-- Estimasi Tiba -->
    @if(!$isCancelled && $order->status !== 'SELESAI')
    <div class="card-warm p-3 mb-4 d-flex align-items-center gap-3">
        <div style="width: 70px; height: 70px; background: var(--cream-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden;">
            <i class="bi bi-image" style="font-size: 2rem; color: var(--text-muted);"></i>
        </div>
        <div class="flex-grow-1">
            <div class="section-label mb-1">ESTIMASI TIBA</div>
            <div class="fw-bold text-brown font-mono" style="font-size: 1.8rem;">
                @if($order->fulfillment_method === 'PICKUP')
                    SIAP AMBIL
                @else
                    15-25 MENIT
                @endif
            </div>
            <small class="text-muted d-flex align-items-center gap-1">
                <i class="bi bi-clock"></i>
                {{ $order->fulfillment_method === 'PICKUP' ? 'Pesanan sedang disiapkan di dapur.' : '~20 Menit Lagi Menuju Lokasi' }}
            </small>
        </div>
    </div>
    @endif

    <!-- Log Status Perjalanan -->
    <div class="mb-4">
        <h5 class="fw-bold text-brown mb-3 font-mono" style="font-size: 0.85rem; letter-spacing: 1px;">
            LOG STATUS PERJALANAN PESANAN
        </h5>

        <div class="position-relative ps-4">
            <div style="position: absolute; left: 19px; top: 20px; bottom: 20px; width: 2px; background: var(--border-color);"></div>

            @php
                $stages = [
                    1 => ['title' => '1. Menunggu Konfirmasi', 'desc' => 'Pesanan masuk dan berhasil diverifikasi oleh kasir outlet.', 'time' => $order->created_at->format('H:i') . ' WIB'],
                    2 => ['title' => '2. Diproses / Dikukus Fresh', 'desc' => 'Sedang dikukus segar di klakat bambu dapur Sukajadi.', 'time' => $order->updated_at->format('H:i') . ' WIB'],
                    3 => ['title' => '3. Menunggu Kurir / Siap Kirim', 'desc' => 'Pengepakan thermal bag dan serah terima ke driver.', 'time' => 'Estimasi'],
                    4 => ['title' => '4. Dalam Pengantaran ke Rumah', 'desc' => 'Kurir meluncur ke titik dropoff pemesan.', 'time' => 'Estimasi'],
                    5 => ['title' => '5. Pesanan Selesai Diterima', 'desc' => 'Pembayaran dilunaskan dan pesanan dinikmati.', 'time' => 'Estimasi']
                ];
            @endphp

            @foreach($stages as $index => $stage)
                @php
                    $isActive = ($currentStage == $index);
                    $isCompleted = ($currentStage > $index);
                    $isPending = ($currentStage < $index);

                    if($isCancelled) {
                        $bgColor = 'var(--cream-dark)';
                        $borderColor = 'var(--border-color)';
                        $iconHtml = '<span class="fw-bold text-muted" style="font-size: 0.9rem;">' . $index . '</span>';
                        $textClass = 'text-muted';
                    } elseif ($isCompleted) {
                        $bgColor = 'var(--green-stock)';
                        $borderColor = 'var(--green-stock)';
                        $iconHtml = '<i class="bi bi-check-lg text-white" style="font-size: 1.2rem;"></i>';
                        $textClass = 'text-brown';
                    } elseif ($isActive) {
                        $bgColor = 'var(--brown-dark)';
                        $borderColor = 'var(--brown-dark)';
                        $iconHtml = '<div style="width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>';
                        $textClass = 'text-brown';
                    } else {
                        $bgColor = 'var(--cream-dark)';
                        $borderColor = 'var(--border-color)';
                        $iconHtml = '<span class="fw-bold text-muted" style="font-size: 0.9rem;">' . $index . '</span>';
                        $textClass = 'text-muted';
                    }
                @endphp

                <div class="d-flex gap-3 mb-4 position-relative">
                    <div style="width: 40px; height: 40px; background: {{ $bgColor }}; border: 2px solid {{ $borderColor }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; z-index: 1; {{ $isActive ? 'animation: pulse 2s infinite;' : '' }}">
                        {!! $iconHtml !!}
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="fw-bold {{ $textClass }}" style="font-size: 0.95rem;">{{ $stage['title'] }}</div>
                            <small class="text-muted font-mono" style="font-size: 0.75rem;">{{ $stage['time'] }}</small>
                        </div>

                        @if($isActive)
                            <span class="badge-stock d-inline-block mt-1" style="background: var(--brown-dark); color: white; font-size: 0.65rem;">AKTIF</span>
                        @endif

                        <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">{{ $stage['desc'] }}</small>

                        @if($isActive && $index == 2)
                            <div class="card-warm p-2 mt-2" style="background: var(--cream); font-size: 0.8rem;">
                                <span class="fw-bold text-brown">Catatan Dapur:</span>
                                <span class="text-muted">Suhu steam 100°C • Timer 12 menit fresh batch.</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Titik Antar & Alamat -->
    <div class="card-warm p-4 mb-3">
        @if($order->fulfillment_method === 'PICKUP')
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-brown font-mono" style="font-size: 0.85rem; letter-spacing: 1px;">LOKASI PENGAMBILAN</h5>
                <span class="badge-stock" style="background: var(--cream-dark); color: var(--brown-dark); font-size: 0.65rem;">[ SELF PICKUP ]</span>
            </div>

            <div class="d-flex gap-2 mb-3">
                <i class="bi bi-shop text-brown mt-1" style="font-size: 1.2rem;"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold text-brown" style="font-size: 0.95rem;">Dapur Th Sant - Outlet Ciumbuleuit</div>
                    <div class="text-muted" style="font-size: 0.85rem; line-height: 1.5;">
                        Jl. Ciumbuleuit No. 88, Hegarmanah, Cidadap, Bandung
                    </div>
                </div>
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-brown font-mono" style="font-size: 0.85rem; letter-spacing: 1px;">TITIK ANTAR & ALAMAT</h5>
                <span class="badge-stock" style="background: var(--green-bg); color: var(--green-stock); font-size: 0.65rem;">[ LOKASI VALID ]</span>
            </div>

            <!-- ✅ PETA GOOGLE MAPS ASLI (SUDAH DIPERBAIKI) -->
            <div class="card-warm mb-3 overflow-hidden" style="background: var(--cream-light); border: 1px solid var(--border-color);">
                <div style="width: 100%; height: 250px; background: #f0f0f0;">
                    @if(!empty($order->shipping_address))
                        <iframe
                            width="100%"
                            height="100%"
                            frameborder="0"
                            scrolling="no"
                            marginheight="0"
                            marginwidth="0"
                            src="https://maps.google.com/maps?q={{ urlencode($order->shipping_address) }}&output=embed"
                            style="filter: grayscale(20%) sepia(10%);">
                        </iframe>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                            <div class="text-center">
                                <i class="bi bi-geo-alt" style="font-size: 2rem;"></i>
                                <p class="mb-0 mt-2">Alamat tidak tersedia</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="p-2 text-center" style="background: white; border-top: 1px solid var(--border-color);">
                    <small class="text-muted font-mono" style="font-size: 0.7rem;">
                        📍 {{ $order->shipping_address }}
                    </small>
                </div>
            </div>

            <div class="d-flex gap-2">
                <i class="bi bi-geo-alt-fill text-brown mt-1" style="font-size: 1.2rem;"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold text-brown" style="font-size: 0.95rem;">Rumah (Penerima: {{ $order->customer_name }})</div>
                    <div class="text-muted" style="font-size: 0.85rem; line-height: 1.5;">
                        {{ $order->shipping_address }}
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                        <strong>Patokan:</strong> Pastikan alamat lengkap dengan patokan yang jelas.
                    </small>
                </div>
            </div>
        @endif
    </div>

    <!-- Ringkasan Item & Pembayaran -->
    <div class="card-warm mb-3">
        <div class="p-3 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#orderSummary" style="cursor: pointer;">
            <div>
                <h5 class="fw-bold mb-0 text-brown font-mono" style="font-size: 0.85rem; letter-spacing: 1px;">RINGKASAN ITEM & PEMBAYARAN</h5>
                <small class="text-muted" style="font-size: 0.8rem;">
                    <strong>{{ $order->orderItems->count() }} Menu</strong> • Rp {{ number_format($order->total_amount, 0, ',', '.') }} ({{ str_replace('_', ' ', $order->payment_method) }})
                </small>
            </div>
            <i class="bi bi-chevron-down text-brown"></i>
        </div>

        <div class="collapse" id="orderSummary">
            <div class="p-3 pt-0">
                <hr class="divider">
                @foreach($order->orderItems as $item)
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <div class="fw-bold text-brown" style="font-size: 0.9rem;">{{ $item->product->name }}</div>
                            <small class="text-muted">{{ $item->quantity }}x @ Rp {{ number_format($item->price_at_purchase ?? $item->product->price, 0, ',', '.') }}</small>
                        </div>
                        <span class="fw-bold text-brown font-mono">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach

                <hr class="divider">

                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Subtotal</span>
                    <span class="font-mono text-brown">Rp {{ number_format($order->subtotal_amount, 0, ',', '.') }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-success">Diskon</span>
                        <span class="font-mono text-success">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Kemasan</span>
                    <span class="font-mono text-brown">Rp 2.000</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">{{ $order->fulfillment_method === 'PICKUP' ? 'Pengambilan' : 'Ongkir' }}</span>
                    <span class="font-mono text-brown">Rp {{ $order->fulfillment_method === 'PICKUP' ? '0' : '10.000' }}</span>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="fw-bold text-brown">TOTAL</span>
                    <span class="fw-bold text-brown font-mono" style="font-size: 1.1rem;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <a href="https://wa.me/6281234567890?text=Halo%20Dapur%20Th%20Sant,%20saya%20mau%20tanya%20order%20{{ $order->order_ref }}"
       class="btn-brown w-100 d-flex align-items-center justify-content-center gap-2 mb-2" style="padding: 16px;">
        <i class="bi bi-chat-square-fill"></i>
        <span class="fw-bold font-mono">[ HUBUNGI WHATSAPP DAPUR ]</span>
    </a>

    <a href="tel:081234567890" class="btn-brown-outline w-100 d-flex align-items-center justify-content-center gap-2" style="padding: 14px;">
        <i class="bi bi-telephone-fill"></i>
        <span class="fw-bold font-mono">[ KONTAK BANTUAN OUTLET ]</span>
    </a>

    <!-- Footer Spec -->
    <div class="text-center py-4 mt-3">
        <small class="font-mono text-muted d-block" style="font-size: 0.65rem;">
            SISTEM MONITORING DAPUR STEAM REALTIME • P6 V11.0
        </small>
        <small class="font-mono text-muted d-block mt-1" style="font-size: 0.65rem;">
            REF ID: {{ $order->order_ref }} // STATUS: {{ $order->status }}
        </small>
    </div>
</div>

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(61, 40, 23, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(61, 40, 23, 0); }
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush
@endif
@endsection
