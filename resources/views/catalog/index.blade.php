@extends('layouts.app')

@section('title', 'Katalog - Toko Dimsum')

@section('navbar')
<div class="top-navbar">
    <div class="logo-section">
        <div class="logo-icon">
            <i class="bi bi-circle-fill" style="font-size: 0.6rem; color: var(--brown-dark);"></i>
        </div>
        <span class="logo-text">KATALOG</span>
    </div>
    <div class="user-icon">
        <i class="bi bi-person-fill"></i>
    </div>
</div>
@endsection

@section('content')
<div class="px-3 pt-3">
    <!-- Hero Promo -->
    <div class="card-warm p-4 mb-3" style="background: linear-gradient(135deg, #f5ede4 0%, #ebe0d3 100%); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -20px; bottom: -20px; opacity: 0.15; font-size: 8rem;">
            <i class="bi bi-circle" style="color: var(--brown-dark);"></i>
        </div>
        <span class="badge-stock mb-2 d-inline-block" style="background: white; color: var(--orange-fav);">PROMO HARIAN</span>
        <h3 class="fw-bold mb-2 text-brown" style="font-size: 1.4rem;">Dimsum Fresh Kukus Siap Kirim</h3>
        <p class="text-muted mb-3" style="font-size: 0.9rem;">Dibuat hangat tiap hari dari daging ayam pilihan tanpa pengawet.</p>
        <a href="#" class="btn-brown d-inline-flex align-items-center gap-2">
            Pesan Sekarang <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    
    <!-- Search Bar -->
    <div class="mb-3">
        <div class="position-relative">
            <i class="bi bi-search position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            <form method="GET" action="{{ route('catalog.index') }}">
                <input type="text" name="search" class="form-control-warm w-100 ps-5" 
                       placeholder="Cari menu dimsum kukus, mentai, goreng..." 
                       value="{{ request('search') }}">
            </form>
        </div>
    </div>
    
    <!-- Category Filter -->
    <div class="d-flex gap-2 mb-4 overflow-auto" style="scrollbar-width: none;">
        <a href="{{ route('catalog.index') }}" 
           class="btn-brown px-3 py-2 text-nowrap {{ !request('category') ? '' : 'btn-brown-outline' }}"
           style="{{ !request('category') ? '' : 'background: white; color: var(--brown-dark);' }}">
            Semua ({{ $categories->sum('products_count') }})
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('catalog.index', ['category' => $cat->id]) }}" 
               class="btn-brown-outline px-3 py-2 text-nowrap"
               style="{{ request('category') == $cat->id ? 'background: var(--brown-dark); color: white;' : 'background: white;' }}">
                {{ $cat->name }} ({{ $cat->products_count }})
            </a>
        @endforeach
    </div>
    
    <!-- Section Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-brown" style="font-size: 1rem; letter-spacing: 1px;">KATALOG PRODUK</h5>
        <small class="text-muted">Tersedia: {{ $products->total() }} Item Aktif</small>
    </div>
    
    <!-- Products Grid -->
    @if($products->count() > 0)
        <div class="row g-3">
            @foreach($products as $index => $product)
                <div class="col-6">
                    <a href="{{ route('catalog.show', $product) }}" class="text-decoration-none">
                        <div class="card-warm h-100">
                            <!-- Image Placeholder -->
                            <div style="background: var(--cream-dark); aspect-ratio: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                                @if($product->image_url)
                                    <img src="{{ asset('storage/' . $product->image_url) }}" 
                                         class="w-100 h-100" style="object-fit: cover; position: absolute; top: 0; left: 0;">
                                @else
                                    <div style="width: 60px; height: 60px; background: var(--cream); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid var(--border-color);">
                                        <i class="bi bi-circle" style="font-size: 1.5rem; color: var(--brown-light);"></i>
                                    </div>
                                    <small class="font-mono mt-2" style="font-size: 0.65rem; color: var(--text-muted);">
                                        FOTO PRODUK 1:1<br>
                                        <span style="font-size: 0.6rem;">(DIMSUM_{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }})</span>
                                    </small>
                                @endif
                            </div>
                            
                            <!-- Content -->
                            <div class="p-3">
                                <!-- Badges Row -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    @if($product->stock > 10)
                                        <span class="badge-stock">READY STOCK</span>
                                    @elseif($product->stock > 0)
                                        <span class="badge-sisa">SISA {{ $product->stock }} PORSI</span>
                                    @else
                                        <span class="badge-sisa">HABIS</span>
                                    @endif
                                    <span class="text-brown" style="font-size: 0.8rem;">
                                        <i class="bi bi-star-fill" style="color: var(--warning-sisa);"></i> 
                                        <strong>{{ number_format(rand(45, 49) / 10, 1) }}</strong>
                                    </span>
                                </div>
                                
                                <!-- Title -->
                                <h6 class="fw-bold mb-1 text-brown" style="font-size: 0.9rem; line-height: 1.3;">
                                    {{ $product->name }}
                                </h6>
                                
                                <!-- Description -->
                                <p class="text-muted mb-2" style="font-size: 0.75rem; line-height: 1.4;">
                                    {{ Str::limit($product->description, 40) }}
                                </p>
                                
                                <!-- Price & Add Button -->
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div>
                                        <div class="section-label mb-0" style="font-size: 0.6rem;">HARGA</div>
                                        <div class="fw-bold text-brown" style="font-size: 1rem;">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('cart.add') }}" onclick="event.stopPropagation();">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn-brown p-2" style="width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        
        @if($products->hasPages())
            <div class="d-flex justify-content-center mt-4 mb-3">
                {{ $products->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 3rem; color: var(--text-muted);"></i>
            <p class="text-muted mt-2">Produk tidak ditemukan</p>
        </div>
    @endif
</div>

<!-- Floating Cart Bar -->
@php
    $cartCount = 0;
    $cartTotal = 0;
    $cart = session()->get('cart', []);
    foreach($cart as $id => $item) {
        $p = \App\Models\Product::find($id);
        if($p) {
            $cartCount += $item['quantity'];
            $cartTotal += $p->price * $item['quantity'];
        }
    }
@endphp

@if($cartCount > 0)
<div class="position-fixed bottom-0 start-0 end-0 p-3" style="max-width: 480px; margin: 0 auto; z-index: 999; bottom: 70px;">
    <a href="{{ route('cart.index') }}" class="btn-brown w-100 d-flex justify-content-between align-items-center" style="padding: 16px 20px; border-radius: 16px;">
        <div class="d-flex align-items-center gap-3">
            <div class="position-relative">
                <i class="bi bi-bag-fill" style="font-size: 1.3rem;"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                    {{ $cartCount }}
                </span>
            </div>
            <div class="text-start">
                <div style="font-size: 0.75rem; opacity: 0.9;">{{ $cartCount }} MENU DIPILIH</div>
                <div class="fw-bold" style="font-size: 1.1rem;">Rp {{ number_format($cartTotal, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span style="font-size: 0.9rem;">Lihat Keranjang</span>
            <i class="bi bi-chevron-right"></i>
        </div>
    </a>
</div>
@endif
@endsection