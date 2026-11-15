@extends('layouts.app')

@section('title', 'Detail Pengiriman - Checkout')

@section('navbar')
<div class="top-navbar">
    <a href="{{ route('cart.index') }}" class="back-btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="page-title">DETAIL PENGIRIMAN</span>
    <div class="user-icon">
        <i class="bi bi-person-fill"></i>
    </div>
</div>
@endsection

@section('content')
<!-- Spec Bar -->
<div class="px-3 py-2 d-flex justify-content-between align-items-center" style="background: var(--cream); border-bottom: 1px solid var(--border-color);">
    <small class="font-mono" style="font-size: 0.65rem; color: var(--text-muted);">WF-SPEC: P4-CHECKOUT-PROMO</small>
    <small class="font-mono" style="font-size: 0.65rem; color: var(--text-muted);">REV 0.9.4 // LO-F1 MONO</small>
</div>

<form method="POST" action="{{ route('orders.store') }}">
    @csrf

    <div class="px-3 pt-3 pb-5">
        <!-- 1. Informasi Pemesan -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-brown" style="font-size: 1rem;">1. INFORMASI PEMESAN</h5>
                <span class="font-mono badge-status" style="font-size: 0.65rem;">REQ_STAGE_1</span>
            </div>

            <div class="mb-3">
                <label class="section-label">NAMA LENGKAP</label>
                <div class="position-relative">
                    <input type="text" name="customer_name" class="form-control-warm w-100 @error('customer_name') is-invalid @enderror"
                           value="{{ old('customer_name') }}" required placeholder="Nama penerima pesanan">
                    @error('customer_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="section-label">NOMOR WHATSAPP</label>
                <div class="position-relative">
                    <input type="text" name="customer_phone" class="form-control-warm w-100 @error('customer_phone') is-invalid @enderror"
                           value="{{ old('customer_phone') }}" required placeholder="08xxxxxxxxxx">
                    @error('customer_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Metode Pemenuhan -->
            <div class="mb-3">
                <label class="section-label">METODE PEMENUHAN</label>
                <div class="d-flex gap-2">
                    <input type="radio" name="fulfillment_method" id="radio-delivery" value="DELIVERY" checked class="d-none" onchange="toggleFulfillment()">
                    <label for="radio-delivery" class="flex-grow-1 card-warm p-3 text-center mb-0" id="label-delivery" style="cursor: pointer; border: 2px solid var(--brown-dark); background: var(--brown-dark); color: white;">
                        <i class="bi bi-check-lg"></i> Delivery
                    </label>

                    <input type="radio" name="fulfillment_method" id="radio-pickup" value="PICKUP" class="d-none" onchange="toggleFulfillment()">
                    <label for="radio-pickup" class="flex-grow-1 card-warm p-3 text-center mb-0" id="label-pickup" style="cursor: pointer;">
                        <i class="bi bi-square"></i> Ambil di Tempat
                    </label>
                </div>
            </div>

            <!-- KOLOM 1: KHUSUS DELIVERY (Alamat) -->
            <div class="mb-3" id="delivery-section">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="section-label mb-0">ALAMAT PENGANTARAN</label>
                </div>
                <div class="card-warm p-3">
                    <div class="d-flex gap-2">
                        <i class="bi bi-geo-alt-fill text-brown mt-1"></i>
                        <div class="flex-grow-1">
                            <textarea name="delivery_address" id="delivery-input" class="form-control-warm w-100 @error('delivery_address') is-invalid @enderror"
                                      rows="2" required placeholder="Jl. Melati No. 14, Sukajadi, Bandung">{{ old('delivery_address') }}</textarea>
                            @error('delivery_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM 2: KHUSUS PICKUP (Catatan Pengambilan) - Tersembunyi Default -->
            <div class="mb-3" id="pickup-section" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="section-label mb-0">CATATAN PENGAMBILAN</label>
                </div>
                <div class="card-warm p-3">
                    <div class="d-flex gap-2">
                        <i class="bi bi-shop text-brown mt-1"></i>
                        <div class="flex-grow-1">
                            <textarea name="pickup_note" id="pickup-input" class="form-control-warm w-100 @error('pickup_note') is-invalid @enderror"
                                      rows="2" placeholder="Contoh: Ambil di kasir, atas nama Budi">{{ old('pickup_note') }}</textarea>
                            @error('pickup_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle"></i> Pesanan dapat diambil di Outlet Dapur Th Sant.
                    </small>
                </div>
            </div>

            <div class="mb-3">
                <label class="section-label">CATATAN TAMBAHAN UNTUK DAPUR (OPSIONAL)</label>
                <textarea name="notes" class="form-control-warm w-100" rows="2"
                          placeholder="Contoh: Saus dipisah, jangan terlalu pedas...">{{ old('notes') }}</textarea>
            </div>
        </div> <!-- ✅ INI TAG PENUTUP YANG SEBELUMNYA HILANG, MENYEBABKAN LAYOUT BERANTAKAN -->

        <!-- 2. Ringkasan Menu Pesanan -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-brown" style="font-size: 1rem;">2. RINGKASAN MENU PESANAN</h5>
                <span class="badge-status">{{ count($cartItems) }} ITEM</span>
            </div>

            @foreach($cartItems as $item)
                <div class="card-warm p-3 mb-2 d-flex gap-3 align-items-center">
                    <div style="width: 60px; height: 60px; flex-shrink: 0; background: var(--cream-dark); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        @if($item['product']->image_url)
                            <img src="{{ asset('storage/' . $item['product']->image_url) }}" class="w-100 h-100" style="object-fit: cover; border-radius: 8px;">
                        @else
                            <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">1:1</span>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-brown" style="font-size: 0.9rem;">{{ $item['product']->name }}</div>
                        <small class="text-muted">{{ $item['product']->description ? Str::limit($item['product']->description, 30) : 'Pedas Sedang' }}</small>
                        <div class="text-muted" style="font-size: 0.75rem;">QTY: {{ $item['quantity'] }}X</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-brown">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 3. Voucher & Promo -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-brown" style="font-size: 1rem;">3. VOUCHER & PROMO</h5>
                <span class="font-mono badge-status" style="font-size: 0.65rem;">PROMO_DISC</span>
            </div>

            <div class="d-flex gap-2 mb-3">
                <input type="text" name="promo_code" class="form-control-warm flex-grow-1 font-mono"
                       placeholder="CONTOH: DISKON10" value="{{ old('promo_code') }}" style="text-transform: uppercase;">
                <button type="button" class="btn-brown px-4" onclick="alert('Kode promo akan divalidasi saat tombol Konfirmasi ditekan')">CEK</button>
            </div>

            @if($promos->count() > 0)
                <div class="text-muted" style="font-size: 0.8rem;">
                    Promo tersedia:
                    @foreach($promos as $promo)
                        <span class="badge-stock ms-1" style="background: var(--cream-dark); color: var(--brown-dark);">{{ $promo->code }}</span>
                    @endforeach
                </div>
            @endif
        </div>

                <!-- 4. Metode Pembayaran -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-brown" style="font-size: 1rem;">4. METODE PEMBAYARAN</h5>
                <span class="font-mono badge-status" style="font-size: 0.65rem;">PAY_SELECT</span>
            </div>

            <!-- Opsi COD -->
            <label class="card-warm p-3 mb-2 d-flex align-items-center gap-3" style="cursor: pointer; border: 2px solid var(--brown-dark);" id="label-cod" onclick="selectPayment('COD')">
                <input type="radio" name="payment_method" value="COD" checked class="d-none" id="radio-cod">
                <div class="flex-grow-1">
                    <div class="fw-bold text-brown">Tunai saat Kurir Tiba (COD)</div>
                    <small class="text-muted" style="font-size: 0.8rem;">Bayar langsung dengan uang pas</small>
                </div>
                <i class="bi bi-check-circle-fill text-brown" id="icon-cod" style="font-size: 1.2rem;"></i>
            </label>

            <!-- Opsi Transfer / QRIS -->
            <label class="card-warm p-3 mb-2 d-flex align-items-center gap-3" style="cursor: pointer;" id="label-transfer" onclick="selectPayment('TRANSFER_BANK')">
                <input type="radio" name="payment_method" value="TRANSFER_BANK" class="d-none" id="radio-transfer">
                <div class="flex-grow-1">
                    <div class="fw-bold text-brown">QRIS / Transfer Bank</div>
                    <small class="text-muted" style="font-size: 0.8rem;">BCA, Mandiri, GoPay, OVO, ShopeePay</small>
                </div>
                <i class="bi bi-circle text-muted" id="icon-transfer" style="font-size: 1.2rem;"></i>
            </label>

            <!-- Pilihan Provider (Muncul hanya jika Transfer dipilih) -->
            <div id="provider-section" class="mt-3 ms-2" style="display: none;">
                <label class="section-label">PILIH PROVIDER PEMBAYARAN <span class="text-danger">*</span></label>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="card-warm p-2 text-center provider-option" style="cursor: pointer; border: 1px solid var(--border-color);" onclick="selectProvider('BCA')">
                            <input type="radio" name="payment_provider" value="BCA" class="d-none" id="prov-bca">
                            <div class="fw-bold text-brown" style="font-size: 0.9rem;">BCA</div>
                            <i class="bi bi-check-circle-fill text-brown d-none" id="icon-prov-bca" style="font-size: 1.1rem;"></i>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="card-warm p-2 text-center provider-option" style="cursor: pointer; border: 1px solid var(--border-color);" onclick="selectProvider('MANDIRI')">
                            <input type="radio" name="payment_provider" value="MANDIRI" class="d-none" id="prov-mandiri">
                            <div class="fw-bold text-brown" style="font-size: 0.9rem;">Mandiri</div>
                            <i class="bi bi-check-circle-fill text-brown d-none" id="icon-prov-mandiri" style="font-size: 1.1rem;"></i>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="card-warm p-2 text-center provider-option" style="cursor: pointer; border: 1px solid var(--border-color);" onclick="selectProvider('GOPAY')">
                            <input type="radio" name="payment_provider" value="GOPAY" class="d-none" id="prov-gopay">
                            <div class="fw-bold text-brown" style="font-size: 0.9rem;">GoPay</div>
                            <i class="bi bi-check-circle-fill text-brown d-none" id="icon-prov-gopay" style="font-size: 1.1rem;"></i>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="card-warm p-2 text-center provider-option" style="cursor: pointer; border: 1px solid var(--border-color);" onclick="selectProvider('OVO')">
                            <input type="radio" name="payment_provider" value="OVO" class="d-none" id="prov-ovo">
                            <div class="fw-bold text-brown" style="font-size: 0.9rem;">OVO</div>
                            <i class="bi bi-check-circle-fill text-brown d-none" id="icon-prov-ovo" style="font-size: 1.1rem;"></i>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="card-warm p-2 text-center provider-option" style="cursor: pointer; border: 1px solid var(--border-color);" onclick="selectProvider('SHOPEEPAY')">
                            <input type="radio" name="payment_provider" value="SHOPEEPAY" class="d-none" id="prov-shopeepay">
                            <div class="fw-bold text-brown" style="font-size: 0.9rem;">ShopeePay</div>
                            <i class="bi bi-check-circle-fill text-brown d-none" id="icon-prov-shopeepay" style="font-size: 1.1rem;"></i>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="card-warm p-2 text-center provider-option" style="cursor: pointer; border: 1px solid var(--border-color);" onclick="selectProvider('QRIS')">
                            <input type="radio" name="payment_provider" value="QRIS" class="d-none" id="prov-qris">
                            <div class="fw-bold text-brown" style="font-size: 0.9rem;">QRIS</div>
                            <i class="bi bi-check-circle-fill text-brown d-none" id="icon-prov-qris" style="font-size: 1.1rem;"></i>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost Breakdown -->
        @php
            $packaging = 2000;
            $shipping = 10000; // Default delivery
            $discount = 0;
            $total = $subtotal + $packaging + $shipping - $discount;
        @endphp

        <div class="card-warm p-4 mb-4">
            <h6 class="fw-bold text-brown mb-3" style="font-size: 0.9rem; letter-spacing: 1px;">RINCIAN PERHITUNGAN BIAYA</h6>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Subtotal Pesanan</span>
                <span class="fw-bold text-brown">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Biaya Kemasan Higienis</span>
                <span class="fw-bold text-brown">Rp {{ number_format($packaging, 0, ',', '.') }}</span>
            </div>

            <!-- Baris Ongkir (Bisa disembunyikan oleh JS) -->
            <div class="d-flex justify-content-between mb-3" id="shipping-row">
                <span class="text-muted">Ongkos Kirim</span>
                <span class="fw-bold text-brown" id="shipping-value">Rp {{ number_format($shipping, 0, ',', '.') }}</span>
            </div>

            <hr class="divider">

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold text-brown" style="font-size: 0.85rem; letter-spacing: 1px;">TOTAL PEMBAYARAN</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-brown" style="font-size: 1.5rem;" id="total-value">Rp {{ number_format($total, 0, ',', '.') }}</div>
                    <span class="badge-status">TERM: NETT</span>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-brown w-100 d-flex justify-content-between align-items-center" style="padding: 16px 24px;">
            <span class="fw-bold">KONFIRMASI PESANAN SEKARANG</span>
            <span class="d-flex align-items-center gap-2">
                <span id="button-total">Rp {{ number_format($total, 0, ',', '.') }}</span> <i class="bi bi-arrow-right"></i>
            </span>
        </button>

        <div class="text-center mt-3">
            <small class="text-muted font-mono" style="font-size: 0.7rem;">
                <i class="bi bi-lock-fill"></i> TRANSAKSI TERENKRIPSI & TERVERIFIKASI OTOMATIS
            </small>
        </div>
    </div>
</form>

@push('scripts')
<script>
        function selectPayment(method) {
        const labelCod = document.getElementById('label-cod');
        const labelTransfer = document.getElementById('label-transfer');
        const iconCod = document.getElementById('icon-cod');
        const iconTransfer = document.getElementById('icon-transfer');
        const providerSection = document.getElementById('provider-section');
        const providerInputs = document.querySelectorAll('input[name="payment_provider"]');

        if (method === 'COD') {
            // Style COD Aktif
            labelCod.style.border = '2px solid var(--brown-dark)';
            iconCod.className = 'bi bi-check-circle-fill text-brown';
            iconCod.style.fontSize = '1.2rem';

            // Style Transfer Non-aktif
            labelTransfer.style.border = '1px solid var(--border-color)';
            iconTransfer.className = 'bi bi-circle text-muted';

            // Sembunyikan & Disable Provider
            providerSection.style.display = 'none';
            providerInputs.forEach(input => {
                input.disabled = true;
                input.required = false;
            });
        } else {
            // Style Transfer Aktif
            labelTransfer.style.border = '2px solid var(--brown-dark)';
            iconTransfer.className = 'bi bi-check-circle-fill text-brown';
            iconTransfer.style.fontSize = '1.2rem';

            // Style COD Non-aktif
            labelCod.style.border = '1px solid var(--border-color)';
            iconCod.className = 'bi bi-circle text-muted';

            // Tampilkan & Enable Provider
            providerSection.style.display = 'block';
            providerInputs.forEach(input => {
                input.disabled = false;
                input.required = true;
            });
        }
    }

    function selectProvider(provider) {
        // Reset semua style provider
        document.querySelectorAll('.provider-option').forEach(el => {
            el.style.border = '1px solid var(--border-color)';
            el.style.background = 'var(--cream)';
        });
        document.querySelectorAll('[id^="icon-prov-"]').forEach(el => {
            el.classList.add('d-none');
        });

        // Aktifkan provider yang dipilih
        const selectedLabel = document.querySelector(`label[onclick="selectProvider('${provider}')"]`);
        const selectedIcon = document.getElementById(`icon-prov-${provider.toLowerCase()}`);

        selectedLabel.style.border = '2px solid var(--brown-dark)';
        selectedLabel.style.background = 'var(--cream-dark)';
        selectedIcon.classList.remove('d-none');
    }

    // Jalankan saat halaman dimuat agar state awal benar
    document.addEventListener('DOMContentLoaded', function() {
        selectPayment('COD'); // Default COD
    });
    function toggleFulfillment() {
        const isPickup = document.getElementById('radio-pickup').checked;

        const deliverySection = document.getElementById('delivery-section');
        const pickupSection = document.getElementById('pickup-section');
        const shippingRow = document.getElementById('shipping-row');

        const deliveryInput = document.getElementById('delivery-input');
        const pickupInput = document.getElementById('pickup-input');

        const labelDelivery = document.getElementById('label-delivery');
        const labelPickup = document.getElementById('label-pickup');

        if (isPickup) {
            // --- MODE AMBIL DI TEMPAT ---
            deliverySection.style.display = 'none';
            pickupSection.style.display = 'block';
            shippingRow.style.display = 'none'; // Sembunyikan ongkir

            // Disable kolom Delivery agar tidak dikirim ke server
            deliveryInput.disabled = true;
            deliveryInput.required = false;

            // Enable kolom Pickup
            pickupInput.disabled = false;
            pickupInput.required = true;

            // Style Tombol
            labelDelivery.style.background = 'transparent';
            labelDelivery.style.color = 'var(--brown-dark)';
            labelDelivery.querySelector('i').className = 'bi bi-square';

            labelPickup.style.background = 'var(--brown-dark)';
            labelPickup.style.color = 'white';
            labelPickup.querySelector('i').className = 'bi bi-check-lg';

            updateTotal(-10000); // Kurangi ongkir

        } else {
            // --- MODE DELIVERY ---
            deliverySection.style.display = 'block';
            pickupSection.style.display = 'none';
            shippingRow.style.display = 'flex'; // Tampilkan ongkir

            // Enable kolom Delivery
            deliveryInput.disabled = false;
            deliveryInput.required = true;

            // Disable kolom Pickup agar tidak dikirim ke server
            pickupInput.disabled = true;
            pickupInput.required = false;

            // Style Tombol
            labelDelivery.style.background = 'var(--brown-dark)';
            labelDelivery.style.color = 'white';
            labelDelivery.querySelector('i').className = 'bi bi-check-lg';

            labelPickup.style.background = 'transparent';
            labelPickup.style.color = 'var(--brown-dark)';
            labelPickup.querySelector('i').className = 'bi bi-square';

            updateTotal(10000); // Tambah ongkir
        }
    }

    function updateTotal(change) {
        const totalEl = document.getElementById('total-value');
        const btnTotalEl = document.getElementById('button-total');

        let currentTotalStr = totalEl.innerText.replace(/\D/g, '');
        let currentTotal = parseInt(currentTotalStr) || 0;
        let newTotal = currentTotal + change;

        let formattedTotal = 'Rp ' + newTotal.toLocaleString('id-ID');
        totalEl.innerText = formattedTotal;
        btnTotalEl.innerText = formattedTotal;
    }

    // Jalankan saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', function() {
        toggleFulfillment();
    });
</script>
@endpush
@endsection
