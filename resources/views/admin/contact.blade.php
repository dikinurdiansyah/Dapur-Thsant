@extends('layouts.admin')

@section('title', 'Kelola Kontak & Outlet')

@section('content')
<!-- Header -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="font-mono" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">
                FR06 // PENGATURAN MASTER // SINKRONISASI REALTIME P7
            </div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem; color: var(--brown-dark);">
                KELOLA INFORMASI KONTAK & PROFIL OUTLET
            </h1>
            <p class="text-muted mb-0">Perubahan informasi di halaman ini akan langsung diperbarui secara realtime pada halaman Kontak Pelanggan (P7) dan footer web publik.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('contact.index') }}" target="_blank" class="btn-admin-outline">
                <i class="bi bi-eye"></i> [ LIHAT LAYAR P7 ]
            </a>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle-fill"></i> Ada kesalahan pada form:
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Status Pipeline -->
<div class="admin-card mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
        <i class="bi bi-lightning-charge-fill text-warning fs-4"></i>
        <div>
            <div class="font-mono fw-bold" style="font-size: 0.85rem;">[ STATUS PIPELINE: LIVE CLOUD SYNC ]</div>
            <small class="text-muted">Target Outlet: <strong>Dapur Ciumbuleuit BDG</strong> | Versi Skema Data: <strong>v2.4.1-rc</strong> | Latensi Replikasi: <strong class="text-success">~240ms</strong></small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <span class="badge-admin" style="background: var(--cream); color: var(--brown-dark);">AUTO-SAVE: OFF</span>
        <span class="badge-admin" style="background: var(--cream); color: var(--brown-dark);">MYSQL://db_local</span>
    </div>
</div>

<!-- ✅ FORM UTAMA YANG MENGHUBUNGKAN KE CONTROLLER -->
<form method="POST" action="{{ route('admin.contact.update') }}" id="formContact">
    @csrf
    <div class="row g-4">
        <!-- 01 Identitas Usaha -->
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="card-header-custom">
                    <div class="card-title-custom">
                        <span class="badge-admin" style="background: var(--brown-dark); color: white;">01</span>
                        IDENTITAS USAHA & WHATSAPP (FR06)
                    </div>
                    <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">[SEC_ID: IDENTITAS_PUB]</span>
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 1.1 | NAMA USAHA / BRAND UTAMA *</label>
                    <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ $data['business_name'] ?? 'Dapur Th Sant — Dimsum & Kuliner UMKM' }}" required>
                    @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted" style="font-size: 0.75rem;">Muncul pada title bar, header invoice, dan meta tags browser.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 1.2 | SLOGAN / TAGLINE KULINER</label>
                    <input type="text" name="tagline" class="form-control @error('tagline') is-invalid @enderror" value="{{ $data['tagline'] ?? 'Dimsum Kukus & Goreng Fresh Halal Tiap Hari Tanpa Pengawet' }}">
                    @error('tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 1.3 | WHATSAPP RESMI (TAP-TO-CHAT) *</label>
                        <div class="input-group">
                            <input type="text" name="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" value="{{ $data['whatsapp'] ?? '081234567890' }}" required placeholder="081234567890">
                            <button class="btn btn-outline-secondary" type="button"><i class="bi bi-whatsapp"></i></button>
                        </div>
                        @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted font-mono" style="font-size: 0.7rem;">Format: 08xxxxxxxxxx (tanpa +62)</small>
                    </div>
                    <div class="col-6">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 1.4 | HOTLINE CADANGAN / KATERING</label>
                        <div class="input-group">
                            <input type="text" name="hotline" class="form-control @error('hotline') is-invalid @enderror" value="{{ $data['hotline'] ?? '' }}" placeholder="081234567890">
                            <button class="btn btn-outline-secondary" type="button"><i class="bi bi-telephone"></i></button>
                        </div>
                        @error('hotline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted" style="font-size: 0.75rem;">Kanal panggilan darurat / manual.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 1.5 | TEMPLATE PESAN PEMBUKA PELANGGAN <span class="text-muted">(URL PRE-FILL)</span></label>
                    <textarea name="wa_template" class="form-control @error('wa_template') is-invalid @enderror" rows="3">{{ $data['wa_template'] ?? 'Halo Admin Dapur Th Sant, saya ingin pesan/tanya seputar menu dimsum kukus & frozen pack...' }}</textarea>
                    @error('wa_template')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted" style="font-size: 0.75rem;">Otomatis diisi saat pelanggan menekan tombol 'Chat via WhatsApp' di layar P7.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 1.6 | EMAIL OPERASIONAL & B2B</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $data['email'] ?? 'kontak@dapurthsant.com' }}">
                    </div>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="text-center font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
                    SCHEMA_TARGET: config.storefront.identity
                </div>
            </div>
        </div>

        <!-- 02 Lokasi Dapur (DENGAN PETA INTERAKTIF) -->
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="card-header-custom">
                    <div class="card-title-custom">
                        <span class="badge-admin" style="background: var(--brown-dark); color: white;">02</span>
                        LOKASI DAPUR & OUTLET UTAMA
                    </div>
                    <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">[SEC_ID: GEO_LOCATION]</span>
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 2.1 | ALAMAT LENGKAP GERAI *</label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" required>{{ $data['address'] ?? 'Jl. Ciumbuleuit No. 88, Hegarmanah, Kec. Cidadap, Kota Bandung, Jawa Barat 40141' }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 2.2 | PATOKAN / CATATAN DRIVER & PEMBELI</label>
                    <input type="text" name="landmark" class="form-control @error('landmark') is-invalid @enderror" value="{{ $data['landmark'] ?? '150m utara Kampus Unpar, seberang mini market kuning' }}">
                    @error('landmark')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 2.3 | LOKASI DI PETA (KLIK UNTUK DAPATKAN KOORDINAT)</label>

                    <!-- Peta Interaktif Leaflet -->
                    <div id="mapLocation" style="height: 250px; border-radius: 8px; margin-bottom: 15px; z-index: 1; border: 1px solid var(--border-color);"></div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label font-mono" style="font-size: 0.7rem;">LATITUDE</label>
                            <div class="input-group">
                                <input type="text" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ $data['latitude'] ?? '-6.879' }}" placeholder="-6.879">
                                <button class="btn btn-outline-secondary" type="button" onclick="getCurrentLocation()" title="Gunakan lokasi browser saat ini">
                                    <i class="bi bi-geo-alt-fill"></i> Saya
                                </button>
                            </div>
                            @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label font-mono" style="font-size: 0.7rem;">LONGITUDE</label>
                            <div class="input-group">
                                <input type="text" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ $data['longitude'] ?? '107.605' }}" placeholder="107.605">
                                <button class="btn btn-outline-secondary" type="button" onclick="searchLocation()" title="Cari berdasarkan nama tempat">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                            @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <small class="text-muted font-mono" style="font-size: 0.7rem;">
                        💡 Klik pada peta atau seret marker biru untuk mendapatkan koordinat otomatis
                    </small>
                </div>

                <div class="text-center font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
                    GEO_PRECISION: EXACT // RADIUS_LIMIT: 25KM
                </div>
            </div>
        </div>

        <!-- 03 Jadwal Operasional -->
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="card-header-custom">
                    <div class="card-title-custom">
                        <span class="badge-admin" style="background: var(--brown-dark); color: white;">03</span>
                        JADWAL OPERASIONAL DAPUR
                    </div>
                    <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">[SEC_ID: OPS_SCHEDULE]</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 3.1 | HARI OPERASIONAL</label>
                        <input type="text" name="operational_days" class="form-control @error('operational_days') is-invalid @enderror" value="{{ $data['operational_days'] ?? 'Selasa - Minggu' }}">
                        @error('operational_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-4">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 3.2 | JAM BUKA</label>
                        <input type="text" name="opening_hours" class="form-control @error('opening_hours') is-invalid @enderror" value="{{ $data['opening_hours'] ?? '09:00 WIB' }}">
                        @error('opening_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-4">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 3.3 | JAM TUTUP</label>
                        <input type="text" name="closing_hours" class="form-control @error('closing_hours') is-invalid @enderror" value="{{ $data['closing_hours'] ?? '21:00 WIB' }}">
                        @error('closing_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 3.4 | HARI KHUSUS / DAPUR OFFLINE</label>
                    <input type="text" name="offline_days" class="form-control @error('offline_days') is-invalid @enderror" value="{{ $data['offline_days'] ?? 'Senin (Khusus Pesanan Katering & PO Terjadwal)' }}">
                    @error('offline_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted" style="font-size: 0.75rem;">Pelanggan tidak bisa order instan regular pada jadwal ini.</small>
                </div>

                <!-- Emergency Toggle (Simulasi Visual) -->
                <div class="p-3 border rounded" style="background: #fde8e8; border-color: #c53030 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold text-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> [ SAKLAR DARURAT TOKO ]
                        </div>
                        <span class="badge-admin badge-completed">STATUS: BUKA (NORMAL)</span>
                    </div>
                    <small class="text-muted d-block mb-2" style="font-size: 0.8rem;">
                        Gunakan jika terjadi kendala produksi mendadak, antrean meluap, atau kehabisan stok dimsum secara total.
                    </small>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_emergency_closed" style="accent-color: #c53030;">
                        <label class="form-check-label text-danger fw-bold" style="font-size: 0.85rem;">
                            [ PAKSA TUTUP SEMENTARA ] (NONAKTIFKAN PEMESANAN SAAT STOK HABIS)
                        </label>
                    </div>
                </div>

                <div class="text-center font-mono mt-3" style="font-size: 0.7rem; color: var(--text-muted);">
                    TIMEZONE: ASIA/JAKARTA (GMT+7)
                </div>
            </div>
        </div>

        <!-- 04 Media Sosial -->
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="card-header-custom">
                    <div class="card-title-custom">
                        <span class="badge-admin" style="background: var(--brown-dark); color: white;">04</span>
                        MEDIA SOSIAL & KEMITRAAN DIGITAL
                    </div>
                    <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">[SEC_ID: SOCIAL_AFFILIATES]</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 4.1 | AKUN INSTAGRAM</label>
                        <div class="input-group">
                            <span class="input-group-text font-mono">ig/</span>
                            <input type="text" name="instagram" class="form-control @error('instagram') is-invalid @enderror" value="{{ $data['instagram'] ?? 'dapurthsant.dimsum' }}">
                        </div>
                        @error('instagram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 4.2 | AKUN TIKTOK</label>
                        <div class="input-group">
                            <span class="input-group-text font-mono">tt/</span>
                            <input type="text" name="tiktok" class="form-control @error('tiktok') is-invalid @enderror" value="{{ $data['tiktok'] ?? 'dapurthsant_official' }}">
                        </div>
                        @error('tiktok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">| 4.3 | LINK AGGREGATOR TERPUSAT</label>
                    <input type="text" name="link_aggregator" class="form-control @error('link_aggregator') is-invalid @enderror" value="{{ $data['link_aggregator'] ?? 'https://linktr.ee/dapurthsant' }}">
                    @error('link_aggregator')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted" style="font-size: 0.75rem;">Kompilasi satu tautan untuk bio Instagram & profil WhatsApp Bisnis.</small>
                </div>

                <div class="d-flex gap-2 mb-3">
                    <div class="flex-grow-1 p-3 border rounded text-center" style="background: var(--cream-light);">
                        <i class="bi bi-qr-code fs-3"></i>
                        <div class="font-mono fw-bold" style="font-size: 0.8rem;">[ QR DINAMIS TAUTAN KONTAK ]</div>
                        <small class="text-muted" style="font-size: 0.7rem;">Auto-generate dari profil publik (P7)</small>
                    </div>
                    <button type="button" class="btn-admin-outline align-self-center">[ EKSPOR SVG ]</button>
                </div>

                <div class="text-center font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
                    CHANNEL_COUNT: 5 CHANNELS VERIFIED
                </div>
            </div>
        </div>
    </div>

    <!-- Save Actions -->
    <div class="admin-card mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-bold">■ STATUS PENGATURAN: TERKONEKSI KE LAYAR PELANGGAN (P7)</div>
                <small class="text-muted">Database MySQL Terhubung • Terakhir Disimpan: {{ now()->format('d M Y, H:i') }} WIB</small>
            </div>
            <div class="d-flex gap-2">
                <button type="reset" class="btn-admin-outline">[ BATAL / RESET ]</button>
                <button type="submit" class="btn-admin"><i class="bi bi-save"></i> [ SIMPAN SEMUA PERUBAHAN (UPDATE LIVE) ]</button>
            </div>
        </div>
    </div>
</form>

<div class="text-center mt-4 font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
    BLUEPRINT: ADMIN_SPEC_v1.0 // WIREFRAME SCHEMATIC // DAPUR TH SANT
    <span class="ms-3">VIEWPORT: 1366 x 768 FIXATED GRID</span>
</div>
@endsection

@push('scripts')
<!-- Leaflet CSS & JS untuk Peta Interaktif -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Inisialisasi Peta
let map, marker;
const defaultLat = {{ $data['latitude'] ?? -6.879 }};
const defaultLng = {{ $data['longitude'] ?? 107.605 }};

function initMap() {
    // Buat peta
    map = L.map('mapLocation').setView([defaultLat, defaultLng], 16);

    // Tambahkan tile layer (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Tambahkan marker yang bisa diseret
    marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);

    // Event saat marker diseret
    marker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });

    // Event saat peta diklik
    map.on('click', function(e) {
        updateCoordinates(e.latlng.lat, e.latlng.lng);
    });
}

// Update koordinat di input field
function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
    marker.setLatLng([lat, lng]);
}

// Dapatkan lokasi user saat ini (GPS Browser)
function getCurrentLocation() {
    if (navigator.geolocation) {
        // Tampilkan loading state sementara
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            updateCoordinates(lat, lng);
            map.setView([lat, lng], 18);
            btn.innerHTML = originalHtml;
        }, function(error) {
            alert('Gagal mendapatkan lokasi. Pastikan izin lokasi diaktifkan di browser Anda.');
            btn.innerHTML = originalHtml;
        });
    } else {
        alert('Browser Anda tidak mendukung fitur geolocation.');
    }
}

// Cari lokasi berdasarkan nama alamat (Geocoding)
function searchLocation() {
    const address = prompt('Masukkan alamat atau nama tempat (contoh: "Unpar Bandung"):');
    if (address) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        // Menggunakan Nominatim OpenStreetMap untuk geocoding gratis
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    updateCoordinates(lat, lng);
                    map.setView([lat, lng], 18);
                } else {
                    alert('Lokasi tidak ditemukan. Coba gunakan kata kunci yang lebih spesifik.');
                }
                btn.innerHTML = originalHtml;
            })
            .catch(error => {
                alert('Terjadi kesalahan saat mencari lokasi.');
                btn.innerHTML = originalHtml;
            });
    }
}

// Inisialisasi peta saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', initMap);
</script>
@endpush
