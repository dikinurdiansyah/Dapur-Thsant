@extends('layouts.app')

@section('title', $product->name . ' - Detail Produk')

@section('navbar')
<div class="top-navbar">
    <a href="{{ route('catalog.index') }}" class="back-btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="page-title">DETAIL PRODUK</span>
    <div class="user-icon">
        <i class="bi bi-person-fill"></i>
    </div>
</div>
@endsection

@section('content')
<!-- Doc ID Bar -->
<div class="px-3 py-2 d-flex justify-content-between align-items-center" style="background: var(--cream); border-bottom: 1px solid var(--border-color);">
    <small class="font-mono" style="font-size: 0.65rem; color: var(--text-muted);">DOC_ID: P2_PRD_DETAIL</small>
    <small class="font-mono" style="font-size: 0.65rem; color: var(--text-muted);">SPEC V1.0.4 [LODI]</small>
</div>

<!-- Badges -->
<div class="px-3 pt-3 d-flex gap-2">
    <span class="badge-stock" style="background: var(--brown-dark); color: white;">BEST SELLER</span>
    <span class="badge-stock" style="background: white; color: var(--brown-dark); border: 1px solid var(--border-color);">100% HALAL</span>
</div>

<!-- Product Image -->
<div class="px-3 pt-3">
    <div class="card-warm" style="aspect-ratio: 4/3; background: var(--cream); display: flex; align-items: center; justify-content: center; position: relative;">
        @if($product->image_url)
            <img src="{{ asset('storage/' . $product->image_url) }}" class="w-100 h-100" style="object-fit: cover; border-radius: 16px;">
        @else
            <div class="text-center">
                <div style="width: 120px; height: 120px; background: var(--cream-dark); border: 2px dashed var(--border-color); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="bi bi-image" style="font-size: 3rem; color: var(--text-muted);"></i>
                </div>
                <small class="font-mono mt-2 d-block" style="font-size: 0.7rem; color: var(--text-muted);">
                    [ Image: Foto Produk Utama ]<br>
                    DIM: 1200 x 900 • RATIO 4:3
                </small>
            </div>
        @endif
        <div class="position-absolute bottom-0 end-0 m-3 badge bg-white text-brown font-mono" style="font-size: 0.7rem;">
            1 / 4 FOTO
        </div>
    </div>
</div>

<!-- Stock & SKU -->
<div class="px-3 pt-3 d-flex justify-content-between align-items-center">
    <span class="badge-stock">STOK TERSEDIA: {{ $product->stock }} PORSI</span>
    <small class="font-mono text-muted" style="font-size: 0.7rem;">SKU: DMS-{{ str_pad($product->id, 3, '0', STR_PAD_LEFT) }}</small>
</div>

<!-- Product Title -->
<div class="px-3 pt-3">
    <h2 class="fw-bold text-brown mb-2" style="font-size: 1.5rem; line-height: 1.3;">{{ $product->name }}</h2>
    
    <!-- Rating -->
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="text-brown">
            <i class="bi bi-star-fill" style="color: var(--warning-sisa);"></i>
            <strong>4.9</strong>
        </span>
        <span class="text-muted" style="font-size: 0.85rem;">(128 ulasan terverifikasi)</span>
        <span class="text-muted">•</span>
        <span class="text-muted" style="font-size: 0.85rem;">Terjual 1.4k+</span>
    </div>
    
    <!-- Price -->
    <div class="card-warm p-3 d-flex align-items-baseline gap-2">
        <span class="fw-bold text-brown" style="font-size: 2rem;">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
        <span class="text-muted" style="font-size: 0.9rem;">/ porsi bambu</span>
        <span class="ms-auto badge-stock" style="background: var(--cream-dark); color: var(--brown-dark);">PPN TERMASUK</span>
    </div>
</div>

<!-- Description -->
<div class="px-3 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-brown" style="font-size: 1rem; letter-spacing: 1px;">DESKRIPSI MENU</h5>
        <small class="font-mono text-muted" style="font-size: 0.7rem;">SPECS SHEET</small>
    </div>
    
    <div class="card-warm p-3">
        <p class="mb-3" style="font-size: 0.9rem; line-height: 1.7; color: var(--text-dark);">
            {{ $product->description ?? 'Dimsum kukus lembut olahan 100% daging pilihan dengan bumbu rempah autentik oriental.' }}
        </p>
        
        <div class="d-flex flex-column gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-square-fill text-brown"></i>
                <span style="font-size: 0.85rem;">Porsi isi 4 butir ukuran medium-large</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-square-fill text-brown"></i>
                <span style="font-size: 0.85rem;">Bebas bahan pengawet & pewarna sintetis</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-square-fill text-brown"></i>
                <span style="font-size: 0.85rem;">Freshly Steamed to Order (Waktu kukus ±8 menit)</span>
            </div>
        </div>
    </div>
</div>

<!-- Sauce Options -->
<div class="px-3 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0 text-brown" style="font-size: 1rem; letter-spacing: 1px;">PILIHAN SAUS PENDAMPING</h5>
            <small class="text-muted" style="font-size: 0.8rem;">Pilih salah satu (Wajib)</small>
        </div>
        <span class="badge-stock" style="background: var(--cream-dark); color: var(--brown-dark);">1 OPSI</span>
    </div>
    
    <div class="d-flex flex-column gap-2">
        <label class="card-warm p-3 d-flex align-items-center gap-3 mb-0" style="cursor: pointer; border: 2px solid var(--brown-dark);">
            <input type="radio" name="sauce" value="chili_1" checked class="form-check-input" style="width: 20px; height: 20px; accent-color: var(--brown-dark);">
            <div class="flex-grow-1">
                <div class="fw-bold text-brown">Chili Oil Level 1 (Sedang)</div>
            </div>
            <span class="badge-stock" style="background: var(--cream-dark); color: var(--brown-dark);">GRATIS</span>
        </label>
        
        <label class="card-warm p-3 d-flex align-items-center gap-3 mb-0" style="cursor: pointer;">
            <input type="radio" name="sauce" value="chili_2" class="form-check-input" style="width: 20px; height: 20px; accent-color: var(--brown-dark);">
            <div class="flex-grow-1">
                <div class="fw-bold text-brown">Chili Oil Level 2 (Pedas)</div>
            </div>
            <span class="badge-stock" style="background: var(--cream-dark); color: var(--brown-dark);">GRATIS</span>
        </label>
        
        <label class="card-warm p-3 d-flex align-items-center gap-3 mb-0" style="cursor: pointer;">
            <input type="radio" name="sauce" value="tomat" class="form-check-input" style="width: 20px; height: 20px; accent-color: var(--brown-dark);">
            <div class="flex-grow-1">
                <div class="fw-bold text-brown">Saus Tomat / Asam Manis</div>
            </div>
            <span class="badge-stock" style="background: var(--cream-dark); color: var(--brown-dark);">GRATIS</span>
        </label>
    </div>
</div>

<!-- Kitchen Notes -->
<div class="px-3 pt-4 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-brown" style="font-size: 1rem; letter-spacing: 1px;">CATATAN PESANAN DAPUR</h5>
        <small class="font-mono text-muted" style="font-size: 0.7rem;">OPSIONAL</small>
    </div>
    
    <textarea class="form-control-warm w-100" rows="3" maxlength="120" 
              placeholder="Contoh: Saus dipisah dalam wadah plastik, kukus sedikit lebih garing..."
              oninput="document.getElementById('charCount').textContent = this.value.length"></textarea>
    <div class="text-end mt-1">
        <small class="font-mono text-muted" style="font-size: 0.7rem;"><span id="charCount">0</span> / 120 Karakter</small>
    </div>
</div>

<!-- Bottom Action Bar - FIXED -->
<div style="position: fixed; bottom: 70px; left: 0; right: 0; z-index: 999; background: var(--cream-light); border-top: 1px solid var(--border-color); padding: 12px 16px; max-width: 480px; margin: 0 auto;">
    <div class="d-flex gap-2 align-items-center">
        <!-- Quantity Control -->
        <div class="qty-control" style="flex: 0 0 auto; display: flex; align-items: center; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; background: white;">
            <button type="button" onclick="changeQty(-1)" style="width: 40px; height: 40px; border: none; background: transparent; color: var(--brown-dark); font-size: 1.2rem; font-weight: 700; cursor: pointer;">
                <i class="bi bi-dash"></i>
            </button>
            <input type="number" id="qty" value="1" min="1" max="{{ $product->stock }}" readonly style="width: 40px; text-align: center; border: none; font-weight: 600; font-size: 1rem; background: transparent;">
            <button type="button" onclick="changeQty(1, {{ $product->stock }})" style="width: 40px; height: 40px; border: none; background: transparent; color: var(--brown-dark); font-size: 1.2rem; font-weight: 700; cursor: pointer;">
                <i class="bi bi-plus"></i>
            </button>
        </div>
        
        <!-- Add to Cart Button -->
        <form method="POST" action="{{ route('cart.add') }}" style="flex-grow: 1; display: flex;">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" id="qty-input" value="1">
            <button type="submit" style="flex-grow: 1; background: var(--brown-dark); color: white; border: none; border-radius: 12px; padding: 12px 20px; font-weight: 600; font-size: 0.95rem; display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                <span>Tambah ke Keranjang</span>
                <span style="font-family: 'JetBrains Mono', monospace; font-weight: 700;">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function changeQty(delta, max = {{ $product->stock }}) {
        const input = document.getElementById('qty');
        const qtyInput = document.getElementById('qty-input');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        if (val > max) val = max;
        input.value = val;
        qtyInput.value = val;
    }
</script>
@endpush
@endsection