@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<!-- Header -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <div class="font-mono" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">
                ■ PUSAT KENDALI DAPUR // LIVE SYNC AKTIF <span class="badge-admin badge-completed">[SYS_NODE_01]</span>
            </div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem; color: var(--brown-dark);">
                Selamat Datang kembali, Admin Dapur Th Sant 👋
            </h1>
            <p class="text-muted mb-0">
                Operasional Hari Ini: {{ now()->format('l, d F Y') }} • Jam Buka 09.00 - 21.00 WIB
            </p>
        </div>
        <!-- ✅ DIPERBAIKI: Menghapus tanda < ganda -->
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products') }}" class="btn-admin" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bi bi-plus-lg"></i> [ + TAMBAH MENU ]
            </a>
            <button class="btn-admin-outline" onclick="window.location.reload()" style="cursor: pointer;">
                <i class="bi bi-arrow-clockwise"></i> [ RE-SYNC DATA ]
            </button>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="kpi-label">KPI_01 // ORDER FLOW <span class="badge-admin badge-completed">[REALTIME]</span></div>
            </div>
            <div class="text-muted" style="font-size: 0.8rem;">ORDER BARU MASUK</div>
            <div class="kpi-value">{{ $stats['pending_orders'] }} Pesanan</div>
            <div class="d-flex gap-3 mt-2" style="font-size: 0.75rem;">
                <div><span style="color: var(--brown-dark);">■</span> {{ $stats['pending_orders'] }} Konfirmasi</div>
                <div><span style="color: var(--orange-fav);">■</span> {{ $stats['total_orders'] - $stats['pending_orders'] - $stats['completed_orders'] }} Sedang Dikukus</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="kpi-label">KPI_02 // REVENUE <span class="badge-admin badge-completed">[+18%]</span></div>
            </div>
            <div class="text-muted" style="font-size: 0.8rem;">TOTAL PENJUALAN HARI INI</div>
            <div class="kpi-value">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
            <div class="d-flex justify-content-between mt-2" style="font-size: 0.75rem;">
                <span class="text-muted">vs Kemarin: Rp 1.258.000</span>
                <span class="text-success fw-bold">+18% ↑</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="kpi-label">KPI_03 // INVENTORY <span class="badge-admin badge-cancelled">[ {{ $lowStockProducts->count() }} Habis ]</span></div>
            </div>
            <div class="text-muted" style="font-size: 0.8rem;">PRODUK AKTIF DI KATALOG</div>
            <div class="kpi-value">{{ $stats['active_products'] }} Menu Aktif</div>
            <div class="d-flex gap-3 mt-2" style="font-size: 0.75rem;">
                <span class="text-danger">{{ $lowStockProducts->count() }} Stok Menipis</span>
                <span class="text-success">{{ $stats['total_products'] - $lowStockProducts->count() }} Siap Jual</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="kpi-label">KPI_04 // VOUCHERS AKTIF: {{ $stats['active_promos'] }}</div>
            </div>
            <div class="text-muted" style="font-size: 0.8rem;">PROMO DIGUNAKAN</div>
            <div class="kpi-value">24x Klaim</div>
            <div class="mt-2" style="font-size: 0.75rem;">
                <div class="d-flex justify-content-between">
                    <span class="font-mono">Kode 'SANT10'</span>
                    <span>Diskon Hemat 10%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Order Queue -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="card-header-custom">
                <div class="card-title-custom">
                    <i class="bi bi-layers"></i> ORDER MASUK PERLU TINDAKAN CEPAT
                    <span class="badge-admin badge-completed">REALTIME</span>
                </div>
                <span class="font-mono" style="font-size: 0.75rem; color: var(--text-muted);">[ {{ $recentOrders->where('status', 'MENUNGGU_KONFIRMASI')->count() }} PESANAN MENUNGGU ]</span>
            </div>

            @forelse($recentOrders->where('status', 'MENUNGGU_KONFIRMASI') as $order)
            <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="font-mono fw-bold" style="font-size: 0.9rem;">#{{ $order->order_ref }}</span>
                        <span class="font-mono ms-2" style="font-size: 0.75rem; color: var(--text-muted);">{{ $order->created_at->format('H:i') }} WIB</span>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge-admin badge-pending">STATUS: {{ str_replace('_', ' ', $order->status) }}</span>
                        <span class="badge-admin" style="background: var(--cream-dark); color: var(--brown-dark);">METODE: {{ str_replace('_', ' ', $order->payment_method) }}</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="font-mono" style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 4px;">NAMA PELANGGAN</div>
                        <div class="fw-bold">{{ $order->customer_name }}</div>
                        <div class="font-mono" style="font-size: 0.8rem; color: var(--text-muted);">WA: {{ $order->customer_phone }}</div>
                    </div>
                    <div class="col-md-7">
                        <div class="font-mono" style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 4px;">DETAIL PESANAN & ITEM MENU</div>
                        @foreach($order->orderItems as $item)
                        <div class="d-flex justify-content-between" style="font-size: 0.85rem;">
                            <span>{{ $item->quantity }}x {{ $item->product->name }}</span>
                            <span class="font-mono">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                        <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                            <span class="fw-bold">TOTAL TAGIHAN</span>
                            <span class="fw-bold font-mono">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                @if($order->rejection_reason)
                <div class="mt-3 p-2" style="background: var(--cream); border-radius: 6px; font-size: 0.85rem;">
                    <span class="font-mono fw-bold">CATATAN:</span> {{ $order->rejection_reason }}
                </div>
                @endif

                <div class="d-flex gap-2 mt-3 justify-content-end">
                    <!-- ✅ DIPERBAIKI: Menambahkan hidden input rejection_reason agar validasi controller terpenuhi -->
                    <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan order ini? Stok akan dikembalikan.')">
                        @csrf
                        <input type="hidden" name="status" value="DIBATALKAN">
                        <input type="hidden" name="rejection_reason" value="Dibatalkan oleh Admin dari Dashboard">
                        <button type="submit" class="btn-admin-outline btn-sm" style="padding: 8px 16px;">
                            [ TOLAK / BATAL ]
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="status" value="DIPROSES">
                        <button type="submit" class="btn-admin btn-sm" style="padding: 8px 16px;">
                            <i class="bi bi-check-lg"></i> [ KONFIRMASI & MULAI KUKUS ]
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                <p class="mt-2">Tidak ada order menunggu konfirmasi</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Stok Kritis -->
        <div class="admin-card">
            <div class="card-header-custom">
                <div class="card-title-custom">
                    <i class="bi bi-exclamation-triangle text-danger"></i> STOK PRODUK KRITIS
                </div>
                <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">RESTOCK_REQ</span>
            </div>

            @forelse($lowStockProducts as $product)
            <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                <div style="width: 40px; height: 40px; background: var(--cream); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                    1:1
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold" style="font-size: 0.9rem;">{{ $product->name }}</div>
                    <div class="text-danger" style="font-size: 0.75rem;">Sisa {{ $product->stock }} porsi lagi</div>
                </div>
                <a href="{{ route('admin.products') }}" class="btn-admin btn-sm" style="padding: 6px 12px; font-size: 0.75rem; text-decoration: none;">
                    [ + TAMBAH ]
                </a>
            </div>
            @empty
            <p class="text-muted text-center">Semua stok aman.</p>
            @endforelse
        </div>

        <!-- Saluran Pesanan -->
        <div class="admin-card">
            <div class="card-header-custom">
                <div class="card-title-custom">
                    <i class="bi bi-bar-chart"></i> SALURAN PESANAN HARI INI
                </div>
                <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">TOTAL: 100%</span>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                    <span>WhatsApp Direct (45%)</span>
                    <span class="font-mono">18 Order</span>
                </div>
                <div class="progress" style="height: 8px; background: var(--cream-dark);">
                    <div class="progress-bar" style="width: 45%; background: var(--brown-dark);"></div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                    <span>Web Katalog Publik (40%)</span>
                    <span class="font-mono">16 Order</span>
                </div>
                <div class="progress" style="height: 8px; background: var(--cream-dark);">
                    <div class="progress-bar" style="width: 40%; background: var(--brown-medium);"></div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                    <span>Ojol Delivery (15%)</span>
                    <span class="font-mono">6 Order</span>
                </div>
                <div class="progress" style="height: 8px; background: var(--cream-dark);">
                    <div class="progress-bar" style="width: 15%; background: var(--brown-light);"></div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3" style="font-size: 0.75rem;">
                <div><span style="color: var(--brown-dark);">■</span> WA</div>
                <div><span style="color: var(--brown-medium);">■</span> Web</div>
                <div><span style="color: var(--brown-light);">■</span> Ojol</div>
            </div>
        </div>

        <!-- Aksi Cepat -->
        <div class="admin-card">
            <div class="card-header-custom">
                <div class="card-title-custom">AKSI CEPAT MANAJEMEN</div>
                <span class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">[QUICK_ACT]</span>
            </div>

            <a href="{{ route('admin.products') }}" class="d-flex align-items-center gap-3 p-3 mb-2 border rounded" style="text-decoration: none; color: var(--text-dark); transition: all 0.2s;" onmouseover="this.style.background='var(--cream)'" onmouseout="this.style.background='white'">
                <i class="bi bi-plus-square fs-4"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold" style="font-size: 0.9rem;">[ + Tambah Menu Baru ]</div>
                    <small class="text-muted">Input foto, varian & stok dapur</small>
                </div>
                <i class="bi bi-arrow-right"></i>
            </a>

            <a href="#" class="d-flex align-items-center gap-3 p-3 mb-2 border rounded" style="text-decoration: none; color: var(--text-dark); transition: all 0.2s;" onmouseover="this.style.background='var(--cream)'" onmouseout="this.style.background='white'">
                <i class="bi bi-file-earmark-spreadsheet fs-4"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold" style="font-size: 0.9rem;">[ Cek Laporan Penjualan ]</div>
                    <small class="text-muted">Ekspor spreadsheet omzet harian</small>
                </div>
                <i class="bi bi-arrow-right"></i>
            </a>

            <a href="{{ route('admin.promos') }}" class="d-flex align-items-center gap-3 p-3 border rounded" style="text-decoration: none; color: var(--text-dark); transition: all 0.2s;" onmouseover="this.style.background='var(--cream)'" onmouseout="this.style.background='white'">
                <i class="bi bi-tag fs-4"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold" style="font-size: 0.9rem;">[ Buka Pengaturan Promo ]</div>
                    <small class="text-muted">Kelola voucher & diskon aktif</small>
                </div>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Efficiency Matrix -->
<div class="admin-card mt-4">
    <div class="d-flex align-items-center gap-3">
        <i class="bi bi-sliders fs-3"></i>
        <div class="flex-grow-1">
            <div class="fw-bold">MATRIKS EFISIENSI OPERASIONAL DAPUR</div>
        </div>
        <span class="font-mono" style="font-size: 0.75rem; color: var(--text-muted);">[ STATUS: ALL_SYSTEMS_OPTIMAL ]</span>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <div class="p-3 border rounded text-center">
                <div class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">RATA-RATA KONFIRMASI</div>
                <div class="fw-bold" style="font-size: 1.5rem;">2.4 mnt</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded text-center">
                <div class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">DURASI KUKUS RATA-RATA</div>
                <div class="fw-bold" style="font-size: 1.5rem;">11.8 mnt</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded text-center">
                <div class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);">TINGKAT KEPUASAN PELANGGAN</div>
                <div class="fw-bold text-success" style="font-size: 1.5rem;">98.5%</div>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-4 font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
    BLUEPRINT: ADMIN_SPEC_v1.0 // WIREFRAME SCHEMATIC // DAPUR TH SANT
    <span class="ms-3">VIEWPORT: 1366 x 768 FIXATED GRID</span>
</div>
@endsection
