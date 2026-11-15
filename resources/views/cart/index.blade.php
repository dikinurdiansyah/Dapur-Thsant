@extends('layouts.app')

@section('title', 'Keranjang - Toko Dimsum')

@section('navbar')
<div class="top-navbar">
    <div class="logo-section">
        <div class="logo-icon" style="background: var(--brown-dark); color: white; border: none;">
            <span class="font-mono fw-bold" style="font-size: 0.8rem;">WF</span>
        </div>
        <span class="logo-text">KERANJANG</span>
    </div>
    <div class="user-icon">
        <i class="bi bi-person-fill"></i>
    </div>
</div>
@endsection

@section('content')
@php
    $subtotal = 0;
    $totalQty = 0;
    foreach($cartItems as $item) {
        $subtotal += $item['subtotal'];
        $totalQty += $item['quantity'];
    }
    $freeShippingTarget = 80000;
    $remaining = max(0, $freeShippingTarget - $subtotal);
    $progress = min(100, ($subtotal / $freeShippingTarget) * 100);
    $packaging = 2000;
    $totalPayment = $subtotal + $packaging;
@endphp

<div class="px-3 pt-3 pb-5">
    @if(count($cartItems) > 0)
        <!-- Free Shipping Progress -->
        <div class="card-warm p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-truck text-brown"></i>
                    <strong class="text-brown" style="font-size: 0.85rem;">
                        @if($remaining > 0)
                            GRATIS ONGKIR: TAMBAH RP {{ number_format($remaining, 0, ',', '.') }} LAGI
                        @else
                            🎉 GRATIS ONGKIR TERCAPAI!
                        @endif
                    </strong>
                </div>
                <span class="font-mono text-muted" style="font-size: 0.8rem;">{{ round($progress) }}%</span>
            </div>
            <div class="progress" style="height: 8px; background: var(--cream-dark); border-radius: 4px;">
                <div class="progress-bar" style="width: {{ $progress }}%; background: var(--brown-dark); border-radius: 4px;"></div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted font-mono" style="font-size: 0.75rem;">Rp {{ number_format($subtotal, 0, ',', '.') }}</small>
                <small class="text-muted font-mono" style="font-size: 0.75rem;">Target: Rp {{ number_format($freeShippingTarget, 0, ',', '.') }}</small>
            </div>
        </div>
        
        <!-- Select All -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <input class="form-check-input" type="checkbox" checked style="width: 20px; height: 20px; accent-color: var(--brown-dark);">
                <label class="fw-bold text-brown" style="font-size: 0.9rem;">
                    Pilih Semua ({{ count($cartItems) }} Item)
                </label>
            </div>
            <form method="POST" action="{{ route('cart.clear') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link text-muted p-0" style="font-size: 0.8rem; text-decoration: none;"
                        onclick="return confirm('Hapus semua item?')">
                    HAPUS TERPILIH
                </button>
            </form>
        </div>
        
        <!-- Cart Items -->
        @foreach($cartItems as $item)
            <div class="card-warm p-3 mb-3">
                <div class="d-flex gap-3">
                    <!-- Checkbox -->
                    <div class="d-flex align-items-start">
                        <input class="form-check-input mt-1" type="checkbox" checked style="width: 20px; height: 20px; accent-color: var(--brown-dark);">
                    </div>
                    
                    <!-- Image -->
                    <div style="width: 80px; height: 80px; flex-shrink: 0;">
                        @if($item['product']->image_url)
                            <img src="{{ asset('storage/' . $item['product']->image_url) }}" class="w-100 h-100" style="object-fit: cover; border-radius: 8px;">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background: var(--cream-dark); border-radius: 8px; border: 1px dashed var(--border-color);">
                                <span class="font-mono" style="font-size: 0.6rem; color: var(--text-muted);">[ X ]</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Info -->
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="fw-bold mb-1 text-brown" style="font-size: 0.95rem;">
                                {{ Str::limit($item['product']->name, 25) }}
                            </h6>
                            <form method="POST" action="{{ route('cart.remove', $item['product']->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 text-muted">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Variant Badge -->
                        <span class="badge-stock mb-2 d-inline-block" style="background: var(--cream-dark); color: var(--brown-dark); font-size: 0.7rem;">
                            Chili oil pisah
                        </span>
                        
                        <div class="text-muted" style="font-size: 0.8rem;">
                            @ Rp {{ number_format($item['product']->price, 0, ',', '.') }} / porsi
                        </div>
                    </div>
                </div>
                
                <!-- Subtotal & Qty -->
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                    <div>
                        <div class="section-label mb-0">SUBTOTAL</div>
                        <div class="fw-bold text-brown" style="font-size: 1.1rem;">
                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('cart.update', $item['product']->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="qty-control">
                            <button type="button" onclick="updateQty(this, -1, 1)"><i class="bi bi-dash"></i></button>
                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $item['product']->stock }}" 
                                   onchange="this.form.submit()" style="width: 40px;">
                            <button type="button" onclick="updateQty(this, 1, {{ $item['product']->stock }})"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
        
        <!-- Voucher -->
        <div class="card-warm p-3 mb-3 d-flex align-items-center gap-3">
            <div style="width: 40px; height: 40px; background: var(--cream-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-tag-fill text-brown"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-brown" style="font-size: 0.9rem;">Voucher Diskon UMKM</div>
                <small class="text-muted" style="font-size: 0.8rem;">Hemat hingga Rp 10.000</small>
            </div>
            <a href="#" class="btn-brown-outline px-3 py-2 d-flex align-items-center gap-2" style="font-size: 0.8rem; padding: 8px 12px;">
                1 PROMO SIAP PAKAI <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        
        <!-- Payment Summary -->
        <div class="card-warm p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-brown" style="font-size: 1.1rem;">Ringkasan Pembayaran</h5>
                <small class="font-mono text-muted" style="font-size: 0.75rem;">DRAFT PO-0924</small>
            </div>
            
            <hr class="divider">
            
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Subtotal Produk</span>
                <span class="fw-bold text-brown">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Biaya Kemasan Higienis</span>
                <span class="fw-bold text-brown">Rp {{ number_format($packaging, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Diskon Promo</span>
                <span class="fw-bold text-brown">-Rp 0</span>
            </div>
            
            <div class="card-warm p-3 mt-3" style="background: var(--cream-dark);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-brown" style="font-size: 0.85rem; letter-spacing: 1px;">TOTAL SEMENTARA</div>
                        <small class="text-muted" style="font-size: 0.75rem;">Termasuk Pajak Resto</small>
                    </div>
                    <div class="fw-bold text-brown" style="font-size: 1.4rem;">
                        Rp {{ number_format($totalPayment, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ✅ TOMBOL CHECKOUT INLINE (PASTI TERLIHAT) -->
        <div class="card-warm p-4 mb-3" style="background: var(--brown-dark); color: white;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div style="font-size: 0.75rem; letter-spacing: 1px; opacity: 0.8;">TOTAL PEMBAYARAN</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-bold" style="font-size: 1.5rem;">Rp {{ number_format($totalPayment, 0, ',', '.') }}</span>
                        <small style="opacity: 0.8;">({{ $totalQty }} Porsi)</small>
                    </div>
                </div>
            </div>
            
            <a href="{{ route('orders.create') }}" class="btn w-100 d-flex justify-content-between align-items-center" 
               style="background: white; color: var(--brown-dark); border: none; border-radius: 12px; padding: 14px 20px; font-weight: 700; font-size: 1rem; text-decoration: none;">
                <span>Lanjut ke Checkout</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        
        <!-- Tombol Lanjut Belanja -->
        <a href="{{ route('catalog.index') }}" class="btn-brown-outline w-100 d-flex justify-content-center align-items-center gap-2 mb-3" style="padding: 14px;">
            <i class="bi bi-arrow-left"></i>
            <span>Lanjut Belanja</span>
        </a>
        
    @else
        <!-- Empty Cart -->
        <div class="text-center py-5">
            <i class="bi bi-bag-x" style="font-size: 4rem; color: var(--text-muted);"></i>
            <h4 class="mt-3 text-brown fw-bold">Keranjang Kosong</h4>
            <p class="text-muted">Yuk, mulai belanja dimsum favoritmu!</p>
            <a href="{{ route('catalog.index') }}" class="btn-brown mt-3">
                <i class="bi bi-grid"></i> Lihat Katalog
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function updateQty(btn, delta, max) {
        const input = btn.parentElement.querySelector('input');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        if (val > max) val = max;
        input.value = val;
        btn.parentElement.parentElement.submit();
    }
</script>
@endpush
@endsection