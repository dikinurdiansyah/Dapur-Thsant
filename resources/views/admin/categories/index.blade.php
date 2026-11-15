@extends('layouts.admin')

@section('title', 'Kelola Kategori')

@section('content')
<div class="mb-4">
    <div class="font-mono" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">
        ■ MODULE // CATEGORY_MGR :: [ LIVE SINKRONISASI KATALOG ]
    </div>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem; color: var(--brown-dark);">
                KELOLA KATEGORI MENU
            </h1>
            <p class="text-muted mb-0">Kategori dipakai untuk mengelompokkan menu pada katalog pelanggan.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn-admin" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
                <i class="bi bi-plus-lg"></i> [ + TAMBAH KATEGORI ]
            </button>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="card-header-custom">
        <div class="card-title-custom">
            <i class="bi bi-folder"></i> DAFTAR KATEGORI
        </div>
        <span class="badge-admin" style="background: var(--cream); color: var(--brown-dark);">
            TOTAL: {{ $categories->total() }} KATEGORI
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-admin table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>NAMA KATEGORI</th>
                    <th>SLUG</th>
                    <th class="text-center">JUMLAH PRODUK</th>
                    <th class="text-end">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $category)
                <tr>
                    <td class="font-mono text-muted" style="font-size: 0.8rem;">
                        {{ str_pad($categories->firstItem() + $index, 2, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>
                        <div class="fw-bold">{{ $category->name }}</div>
                        <small class="text-muted font-mono" style="font-size: 0.7rem;">
                            Dibuat {{ $category->created_at?->format('d M Y') ?? '-' }}
                        </small>
                    </td>
                    <td class="font-mono" style="font-size: 0.8rem;">{{ $category->slug }}</td>
                    <td class="text-center">
                        <span class="badge-admin" style="background: var(--cream-dark); color: var(--brown-dark);">
                            {{ $category->products_count }} produk
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn-admin-outline btn-sm" style="padding: 4px 12px; font-size: 0.75rem;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditCategory{{ $category->id }}">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                  onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm"
                                        style="background: #fde8e8; color: #c53030; border: none; padding: 4px 12px; font-size: 0.75rem; border-radius: 6px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <div class="modal fade" id="modalEditCategory{{ $category->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-header" style="background: var(--cream); border-bottom: 1px solid var(--border-color);">
                                    <h5 class="modal-title fw-bold">[ EDIT KATEGORI ]</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">NAMA KATEGORI *</label>
                                        <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                    </div>
                                    <div>
                                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">SLUG (OPSIONAL)</label>
                                        <input type="text" name="slug" class="form-control" value="{{ $category->slug }}" placeholder="otomatis dari nama">
                                        <small class="text-muted" style="font-size: 0.75rem;">Kosongkan untuk membuat ulang dari nama.</small>
                                    </div>
                                </div>
                                <div class="modal-footer" style="background: var(--cream-light); border-top: 1px solid var(--border-color);">
                                    <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">[ BATAL ]</button>
                                    <button type="submit" class="btn-admin">
                                        <i class="bi bi-save"></i> [ SIMPAN ]
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2 mb-0">Belum ada kategori. Tambahkan kategori pertama Anda.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $categories->links('pagination::bootstrap-5') }}
</div>

<div class="modal fade" id="modalAddCategory" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="modal-header" style="background: var(--cream); border-bottom: 1px solid var(--border-color);">
                    <h5 class="modal-title fw-bold">[ + TAMBAH KATEGORI ]</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">NAMA KATEGORI *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required placeholder="Contoh: Dimsum Kukus">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label font-mono" style="font-size: 0.75rem; font-weight: 700;">SLUG (OPSIONAL)</label>
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                               value="{{ old('slug') }}" placeholder="otomatis dari nama">
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--cream-light); border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">[ BATAL ]</button>
                    <button type="submit" class="btn-admin">
                        <i class="bi bi-save"></i> [ SIMPAN KATEGORI ]
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="text-center mt-4 font-mono" style="font-size: 0.7rem; color: var(--text-muted);">
    BLUEPRINT: ADMIN_SPEC_v1.0 // CATEGORY_MASTER // DAPUR TH SANT
</div>
@endsection
