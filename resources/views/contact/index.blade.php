@extends('layouts.app')

@section('title', 'Kontak - ' . ($data['business_name'] ?? 'Dapur Th Sant'))

@section('navbar')
<div class="top-navbar">
    <div class="logo-section">
        <div class="logo-icon" style="background: var(--brown-dark); color: white; border: none; border-radius: 10px;">
            <span class="font-mono fw-bold" style="font-size: 1rem;">#</span>
        </div>
        <span class="logo-text">KONTAK</span>
    </div>
    <div class="user-icon">
        <i class="bi bi-person-fill"></i>
    </div>
</div>
@endsection

@section('content')
<div class="px-3 pt-3 pb-5">
    <!-- Outlet Banner -->
    <div class="card-warm mb-3" style="background: linear-gradient(135deg, #ebe0d3 0%, #ddd0c0 100%); aspect-ratio: 358/140; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden;">
        <div style="position: absolute; inset: 0; border: 2px dashed var(--border-color); border-radius: 16px; margin: 8px;"></div>
        <div class="text-center">
            <div class="font-mono fw-bold text-brown" style="font-size: 0.85rem; letter-spacing: 1px;">
                {{ $data['business_name'] ?? 'DAPUR TH SANT' }}
            </div>
            <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">{{ $data['tagline'] ?? 'Dimsum Kukus Segar & Kuliner Tradisional UMKM' }}</small>
        </div>
    </div>

    <!-- Outlet Profile Card -->
    <div class="card-warm p-4 mb-3">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div style="width: 60px; height: 60px; background: var(--cream-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 2px solid var(--border-color);">
                <i class="bi bi-circle-fill" style="font-size: 1.5rem; color: var(--brown-light);"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex gap-2 mb-1">
                    <span class="badge-stock" style="background: var(--brown-dark); color: white;">VERIFIED</span>
                    <span class="badge-stock" style="background: var(--cream-dark); color: var(--brown-dark);">KULINER BDG</span>
                </div>
                <h4 class="fw-bold text-brown mb-0" style="font-size: 1.2rem;">{{ $data['business_name'] ?? 'Dapur Th Sant' }}</h4>
                <small class="text-muted">{{ $data['tagline'] ?? 'Dimsum & Kuliner UMKM Bandung' }}</small>
            </div>
        </div>

        <!-- Live Status -->
        <div class="d-flex align-items-center gap-2 mb-3 p-2" style="background: var(--cream-light); border-radius: 10px;">
            <span style="width: 10px; height: 10px; background: var(--green-stock); border-radius: 50%;"></span>
            <span class="fw-bold text-brown" style="font-size: 0.85rem;">
                BUKA HARI INI: {{ $data['opening_hours'] ?? '09:00' }} - {{ $data['closing_hours'] ?? '21:00' }} WIB
            </span>
            <span class="badge-stock ms-auto" style="background: var(--green-bg); color: var(--green-stock);">LIVE STATUS</span>
        </div>

        <!-- Stats Row -->
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="card-warm p-2 text-center" style="background: var(--cream-light);">
                    <div class="text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">⭐ RATING</div>
                    <div class="fw-bold text-brown" style="font-size: 1.1rem;">4.9 / 5.0</div>
                    <small class="text-muted" style="font-size: 0.7rem;">1.4k Ulasan</small>
                </div>
            </div>
            <div class="col-4">
                <div class="card-warm p-2 text-center" style="background: var(--cream-light);">
                    <div class="text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">🔥 KUKUS</div>
                    <div class="fw-bold text-brown" style="font-size: 1.1rem;">15-25 Mnt</div>
                    <small class="text-muted" style="font-size: 0.7rem;">Rata-rata</small>
                </div>
            </div>
            <div class="col-4">
                <div class="card-warm p-2 text-center" style="background: var(--cream-light);">
                    <div class="text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">✓ STATUS</div>
                    <div class="fw-bold text-brown" style="font-size: 1.1rem;">100%</div>
                    <small class="text-muted" style="font-size: 0.7rem;">Halal MUI</small>
                </div>
            </div>
        </div>

        <!-- WhatsApp Button -->
        <div class="card-warm p-3 mb-3 d-flex align-items-center gap-3" style="background: var(--brown-dark); color: white;">
            <div style="width: 40px; height: 40px; background: #25D366; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-whatsapp" style="font-size: 1.3rem;"></i>
            </div>
            <div class="flex-grow-1">
                <div style="font-size: 0.7rem; letter-spacing: 1px; opacity: 0.8;">CHAT WHATSAPP RESMI</div>
                <div class="font-mono fw-bold" style="font-size: 1rem;">{{ $data['whatsapp'] ?? '0812-3456-7890' }}</div>
            </div>
            <span class="badge-stock" style="background: rgba(255,255,255,0.2); color: white;">[ FAST RESP ]</span>
        </div>

        <!-- Action Buttons -->
        <div class="row g-2">
            <div class="col-6">
                <a href="tel:{{ preg_replace('/[^0-9]/', '', $data['whatsapp'] ?? '081234567890') }}" class="btn-brown-outline w-100 d-flex align-items-center justify-content-center gap-2" style="padding: 12px;">
                    <i class="bi bi-telephone-fill"></i>
                    <span style="font-size: 0.8rem;">TELEPON LANGSUNG</span>
                </a>
            </div>
            <div class="col-6">
                <a href="https://maps.google.com/?q={{ $data['latitude'] ?? '-6.879' }},{{ $data['longitude'] ?? '107.605' }}" target="_blank" class="btn-brown-outline w-100 d-flex align-items-center justify-content-center gap-2" style="padding: 12px;">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span style="font-size: 0.8rem;">PETUNJUK ARAH</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Lokasi & Gerai Fisik -->
    <div class="card-warm p-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-brown d-flex align-items-center gap-2" style="font-size: 1rem;">
                <i class="bi bi-geo-alt-fill"></i> LOKASI & GERAI FISIK
            </h5>
            <span class="font-mono badge-status" style="font-size: 0.65rem;">[ OUTLET #01 ]</span>
        </div>

        <div class="mb-3">
            <p class="mb-1 text-brown" style="font-size: 0.9rem; line-height: 1.5;">
                {{ $data['address'] ?? 'Jl. Ciumbuleuit No. 88, Hegarmanah, Cidadap, Kota Bandung, Jawa Barat 40141' }}
            </p>
            @if(!empty($data['landmark']))
                <small class="text-muted" style="font-size: 0.8rem;">
                    <strong>Patokan:</strong> {{ $data['landmark'] }}
                </small>
            @endif
        </div>

        <!-- Map dengan Koordinat Dinamis -->
        <div class="card-warm mb-3" style="background: var(--cream-light); aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center; position: relative; border: 2px dashed var(--border-color); overflow: hidden;">
            @if(!empty($data['latitude']) && !empty($data['longitude']))
                <iframe
                    width="100%"
                    height="100%"
                    frameborder="0"
                    scrolling="no"
                    marginheight="0"
                    marginwidth="0"
                    src="https://maps.google.com/maps?q={{ $data['latitude'] }},{{ $data['longitude'] }}&z=15&output=embed">
                </iframe>
            @else
                <div class="text-center">
                    <div class="font-mono fw-bold text-brown" style="font-size: 0.85rem;">
                        [ PETA LOKASI OUTLET CIUMBULEUIT ]
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                        LAT: {{ $data['latitude'] ?? '-' }}, LONG: {{ $data['longitude'] ?? '-' }}
                    </small>
                </div>
            @endif
        </div>

        <button class="btn-brown-outline w-100 d-flex align-items-center justify-content-center gap-2" style="padding: 12px;" onclick="navigator.clipboard.writeText('{{ $data['address'] ?? '' }}'); alert('Alamat disalin!')">
            <i class="bi bi-clipboard"></i>
            <span style="font-size: 0.85rem;">SALIN ALAMAT LENGKAP</span>
        </button>
    </div>

    <!-- Jadwal Operasional -->
    <div class="card-warm p-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-brown d-flex align-items-center gap-2" style="font-size: 1rem;">
                <i class="bi bi-clock"></i> JADWAL OPERASIONAL
            </h5>
            <span class="font-mono badge-status" style="font-size: 0.65rem;">[ WIB / GMT+7 ]</span>
        </div>

        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border-color);">
            <div class="d-flex align-items-center gap-2">
                <span style="width: 8px; height: 8px; background: var(--green-stock); border-radius: 50%;"></span>
                <span class="fw-bold text-brown" style="font-size: 0.9rem;">{{ $data['operational_days'] ?? 'Selasa - Minggu' }}</span>
            </div>
            <span class="font-mono fw-bold text-brown" style="font-size: 0.9rem;">
                {{ $data['opening_hours'] ?? '09:00' }} - {{ $data['closing_hours'] ?? '21:00' }} WIB
            </span>
        </div>

        @if(!empty($data['offline_days']))
        <div class="d-flex justify-content-between align-items-center py-2">
            <div class="d-flex align-items-center gap-2">
                <span style="width: 8px; height: 8px; background: var(--orange-fav); border-radius: 50%;"></span>
                <div>
                    <div class="fw-bold text-brown" style="font-size: 0.9rem;">Senin</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Outlet Dine-In Tutup</small>
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-brown" style="font-size: 0.85rem;">Khusus Katering / PO</div>
                <small class="text-muted font-mono" style="font-size: 0.7rem;">MIN. H-1 PEMESANAN</small>
            </div>
        </div>
        <div class="mt-2 p-2 rounded" style="background: var(--warning-bg);">
            <small class="text-danger fst-italic d-block">* {{ $data['offline_days'] }}</small>
        </div>
        @endif
    </div>

    <!-- Media Sosial & Partner -->
    <div class="card-warm p-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-brown d-flex align-items-center gap-2" style="font-size: 1rem;">
                <i class="bi bi-share-fill"></i> MEDIA SOSIAL & PARTNER
            </h5>
            <span class="font-mono badge-status" style="font-size: 0.65rem;">KANAL RESMI</span>
        </div>

        <!-- Instagram -->
        @if(!empty($data['instagram']))
        <div class="d-flex align-items-center gap-3 py-2" style="border-bottom: 1px solid var(--border-color);">
            <div style="width: 40px; height: 40px; background: #E1306C; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                <i class="bi bi-instagram"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-brown" style="font-size: 0.9rem;">Instagram</div>
                <small class="text-muted" style="font-size: 0.8rem;">{{ $data['instagram'] }}</small>
            </div>
            <a href="https://instagram.com/{{ str_replace('@', '', $data['instagram']) }}" target="_blank" class="btn-brown-outline px-3 py-2" style="font-size: 0.75rem; padding: 6px 12px;">KUNJUNGI</a>
        </div>
        @endif

        <!-- TikTok -->
        @if(!empty($data['tiktok']))
        <div class="d-flex align-items-center gap-3 py-2" style="border-bottom: 1px solid var(--border-color);">
            <div style="width: 40px; height: 40px; background: #000; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                <i class="bi bi-music-note-beamed"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-brown" style="font-size: 0.9rem;">TikTok</div>
                <small class="text-muted" style="font-size: 0.8rem;">{{ $data['tiktok'] }}</small>
            </div>
            <a href="https://tiktok.com/{{ str_replace('@', '', $data['tiktok']) }}" target="_blank" class="btn-brown-outline px-3 py-2" style="font-size: 0.75rem; padding: 6px 12px;">IKUTI</a>
        </div>
        @endif

        <!-- Link Aggregator (Linktree) -->
        @if(!empty($data['link_aggregator']))
        <div class="d-flex align-items-center gap-3 py-2">
            <div style="width: 40px; height: 40px; background: var(--brown-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                <i class="bi bi-link-45deg"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-brown" style="font-size: 0.9rem;">LinkTree</div>
                <small class="text-muted" style="font-size: 0.8rem;">Semua Link dalam Satu</small>
            </div>
            <a href="{{ $data['link_aggregator'] }}" target="_blank" class="btn-brown-outline px-3 py-2" style="font-size: 0.75rem; padding: 6px 12px;">BUKA</a>
        </div>
        @endif
    </div>

    <!-- Form Tanya Katering -->
    <div class="card-warm p-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-brown d-flex align-items-center gap-2" style="font-size: 1rem;">
                <i class="bi bi-envelope-fill"></i> TANYA KATERING & PESANAN
            </h5>
            <span class="font-mono badge-status" style="font-size: 0.65rem;">[ FORM INQUIRY ]</span>
        </div>

        <form id="inquiryForm">
            <div class="mb-3">
                <label class="section-label">NAMA PEMESAN / PIC <span class="text-danger">*</span></label>
                <input type="text" id="inquiryName" class="form-control-warm w-100" placeholder="Contoh: Ibu Rina / Bpk. Hendra" required>
            </div>

            <div class="mb-3">
                <label class="section-label">JENIS KEBUTUHAN <span class="text-danger">*</span></label>
                <select id="inquiryNeed" class="form-control-warm w-100" required>
                    <option value="">Pilih Kategori Kebutuhan</option>
                    <option value="katering">Katering Harian</option>
                    <option value="acara">Acara / Event</option>
                    <option value="ulang_tahun">Ulang Tahun</option>
                    <option value="rapat">Rapat Kantor</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="section-label">DETAIL PESANAN / TANGGAL ACARA <span class="text-danger">*</span></label>
                <textarea id="inquiryDetail" class="form-control-warm w-100" rows="4" placeholder="Tuliskan estimasi jumlah porsi, tanggal acara, atau pertanyaan spesifik..." required></textarea>
            </div>

            <button type="submit" class="btn-brown w-100 d-flex align-items-center justify-content-center gap-2" style="padding: 16px;">
                <i class="bi bi-whatsapp"></i>
                <span class="fw-bold">KIRIM PESAN VIA WHATSAPP</span>
            </button>

            <small class="text-muted d-block text-center mt-2" style="font-size: 0.75rem;">
                Formulir otomatis terhubung ke WhatsApp customer service kami.
            </small>
        </form>
    </div>

    <!-- Footer Spec -->
    <div class="text-center py-3">
        <small class="font-mono text-muted" style="font-size: 0.65rem;">
            FRAME: SCREEN_47 // P7_KONTAK &nbsp;•&nbsp; STATUS: SPEC_APPROVED
        </small>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('inquiryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('inquiryName').value;
        const need = document.getElementById('inquiryNeed').value;
        const detail = document.getElementById('inquiryDetail').value;
        const message = `Halo {{ $data['business_name'] ?? 'Dapur Th Sant' }}, saya ${name}. Kebutuhan: ${need}. Detail: ${detail}`;
        const phone = '{{ preg_replace('/[^0-9]/', '', $data['whatsapp'] ?? '081234567890') }}';
        window.open(`https://wa.me/62${phone}?text=${encodeURIComponent(message)}`, '_blank');
    });
</script>
@endpush
@endsection
