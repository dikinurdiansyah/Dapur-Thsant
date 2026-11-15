@extends('layouts.admin')

@section('title', 'Kelola Produk & Kategori')

@section('content')
<!-- Header -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="font-mono" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">
                ■ MODULE // PRODUCT_CATALOG_MGR :: [ LIVE SINKRONISASI P2 ]
            </div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem; color: var(--brown-dark);">
                KELOLA PRODUK & KATEGORI DIMSUM
            </h1>
            <p class="text-muted mb-0">Tambah, edit, atau nonaktifkan menu yang tampil di katalog pelanggan.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn-admin" data-bs-toggle="modal" data-bs-target="#modalAddProduct">
                <i class="bi bi-plus-lg"></i> [ + TAMBAH MENU BARU ]
            </button>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-box-seam"></i> TOTAL MENU AKTIF</div>
            <div class="kpi-value">{{ $products->where('is_active', true)->count() }} Item</div>
            <div class="text-muted" style="font-size: 0.8rem;">Siap dijual di katalog publik</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-exclamation-triangle"></i> STOK MENIPIS</div>
            <div class="kpi-value" style="color: var(--orange-fav);">{{ $products->where('stock', '<', 10)->count() }} Item</div>
            <div class="text-muted" style="font-size: 0.8rem;">Perlu restock segera (&lt; 10 porsi)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-folder"></i> TOTAL KATEGORI</div>
            <div class="kpi-value">{{ $categories->count() }} Kategori</div>
            <div class="text-muted" style="font-size: 0.8rem;">Pengelompokan menu</div>
        </div>
    </div>
</div>

<!-- Product Table -->
<div class="admin-card">
    <div class="card-header-custom">
        <div class="card-title-custom">
            <i class="bi bi-list-ul"></i> DAFTAR MENU DIMSUM
        </div>
        <div class="d-flex gap-2">
            <input type="text" id="searchProduct" class="form-control form-control-sm" placeholder="🔍 Cari nama produk..." style="width: 250px;" onkeyup="filterProducts()">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-admin table-hover align-middle" id="productTable">
            <thead>
                <tr>
                    <th style="width: 80px;">FOTO</th>
                    <th>NAMA PRODUK</th>
                    <th>KATEGORI</th>
                    <th>HARGA</th>
                    <th>STOK</th>
                    <th>STATUS</th>
                    <th class="text-end">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="product-row" data-name="{{ strtolower($product->name) }}">
                    <td>
                        @if($product->image_url)
                            <img src="{{ asset('storage/' . $product->image_url) }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                        @else
                            <div style="width: 50px; height: 50px; background: var(--cream); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold">{{ $product->name }}</div>
                        <small class="text-muted font-mono" style="font-size: 0.7rem;">SKU: {{ $product->slug }}</small>
                    </td>
                    <td>
                        <span class="badge-admin" style="background: var(--cream); color: var(--brown-dark);">
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </span>
                    </td>
                    <td class="fw-bold font-mono">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    <td>
                        @if($product->stock < 10)
                            <span class="text-danger fw-bold">{{ $product->stock }}</span>
                        @else
                            <span class="fw-bold">{{ $product->stock }}</span>
                        @endif
                    </td>
                    <td>
                        @if($product->is_active)
                            <span class="badge-admin badge-completed">■ Aktif</span>
                        @else
                            <span class="badge-admin badge-cancelled">■ Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn-admin-outline btn-sm" style="padding: 4px 12px; font-size: 0.75rem;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditProduct{{ $product->id }}">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="background: #fde8e8; color: #c53030; border: none; padding: 4px 12px; font-size: 0.75rem; border-radius: 6px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Modal Edit Produk (Satu modal per produk) -->
                <div class="modal fade" id="modalEditProduct{{ $product->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-header" style="background: var(--cream); border-bottom: 1px solid var(--border-color);">
                                    <h5 class="modal-title fw-bold">[ EDIT MENU: {{ $product->name }} ]</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">NAMA PRODUK *</label>
                                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">KATEGORI *</label>
                                            <select name="category_id" class="form-select" required>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">HARGA (Rp) *</label>
                                            <input type="number" name="price" class="form-control" value="{{ $product->price }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">STOK *</label>
                                            <input type="number" name="stock" class="form-control" value="{{ $product->stock }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">DESKRIPSI</label>
                                            <textarea name="description" class="form-control" rows="3">{{ $product->description }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">FOTO PRODUK (Kosongkan jika tidak ingin mengganti)</label>
                                            <input type="file" name="image" class="form-control" accept="image/*">
                                            @if($product->image_url)
                                                <small class="text-muted d-block mt-1">Foto saat ini: <a href="{{ asset('storage/' . $product->image_url) }}" target="_blank">Lihat</a></small>
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check p-3 border rounded" style="background: var(--cream);">
                                                <input class="form-check-input" type="checkbox" name="is_active" {{ $product->is_active ? 'checked' : '' }} style="accent-color: var(--brown-dark);">
                                                <label class="form-check-label font-mono" style="font-size: 0.8rem;">
                                                    [X] PRODUK AKTIF (TAMPIL DI KATALOG)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer" style="background: var(--cream-light); border-top: 1px solid var(--border-color);">
                                    <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">[ BATAL ]</button>
                                    <button type="submit" class="btn-admin">
                                        <i class="bi bi-save"></i> [ SIMPAN PERUBAHAN ]
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2">Belum ada produk ditambahkan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Produk (SUDAH DIPERBAIKI DENGAN VALIDASI ERROR) -->
<div class="modal fade" id="modalAddProduct" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="background: var(--cream); border-bottom: 1px solid var(--border-color);">
                    <h5 class="modal-title fw-bold">[ + TAMBAH MENU BARU ]</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">NAMA PRODUK *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Contoh: Dimsum Ayam Original">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">KATEGORI *</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">HARGA (Rp) *</label>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required placeholder="15000">
                            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">STOK AWAL *</label>
                            <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock') }}" required placeholder="50">
                            @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">DESKRIPSI</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Deskripsi singkat produk...">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">FOTO PRODUK</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted" style="font-size: 0.75rem;">Format: JPG, PNG. Maksimal 2MB.</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check p-3 border rounded" style="background: var(--cream);">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }} style="accent-color: var(--brown-dark);">
                                <label class="form-check-label font-mono" style="font-size: 0.8rem;">
                                    [X] LANGSUNG AKTIFKAN DI KATALOG
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--cream-light); border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">[ BATAL ]</button>
                    <button type="submit" class="btn-admin">
                        <i class="bi bi-save"></i> [ SIMPAN PRODUK ]
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="text-center mt-4 font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
    BLUEPRINT: ADMIN_SPEC_v1.0 // WIREFRAME SCHEMATIC // DAPUR TH SANT
</div>

@push('scripts')
<script>
function filterProducts() {
    const input = document.getElementById('searchProduct').value.toLowerCase();
    const rows = document.querySelectorAll('.product-row');
    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        row.style.display = name.includes(input) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
