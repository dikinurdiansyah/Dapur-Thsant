<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promo;
use App\Models\Setting; // ✅ TAMBAHAN: Import Model Setting
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // ==================== DASHBOARD ====================
    public function dashboard()
    {
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_categories' => Category::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'MENUNGGU_KONFIRMASI')->count(),
            'completed_orders' => Order::where('status', 'SELESAI')->count(),
            'total_revenue' => Order::where('status', 'SELESAI')->sum('total_amount'),
            'active_promos' => Promo::where('is_active', true)->count(),
            'pending_payments' => Payment::where('payment_status', 'PENDING_REVIEW')->count(),
        ];

        $recentOrders = Order::with(['payment', 'orderItems.product'])
            ->latest()
            ->take(5)
            ->get();

        $lowStockProducts = Product::where('stock', '<', 10)
            ->with('category')
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'lowStockProducts'));
    }

    // ==================== CATEGORIES ====================
    public function categories()
    {
        $categories = Category::withCount('products')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Category::create($validated);

        return redirect()->route('admin.categories')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyCategory(Category $category)
    {
        $productCount = $category->products()->count();

        if ($productCount > 0) {
            return back()->with('error', "Tidak bisa hapus kategori yang memiliki {$productCount} produk.");
        }

        $category->delete();

        return redirect()->route('admin.categories')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    // ==================== PRODUCTS ====================
    public function products()
    {
        $products = Product::with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:max_width=4000,max_height=4000',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image_url'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');

        Product::create($validated);

        return redirect()->route('admin.products')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:max_width=4000,max_height=4000',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            if ($product->image_url) {
                Storage::disk('public')->delete($product->image_url);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image_url'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()->route('admin.products')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    // ✅ PERBAIKAN DI SINI: Cek riwayat pesanan sebelum menghapus
    public function destroyProduct(Product $product)
    {
        // Cek apakah produk ini sudah pernah dipesan (ada di tabel order_items)
        $hasOrderHistory = \App\Models\OrderItem::where('product_id', $product->id)->exists();

        if ($hasOrderHistory) {
            return redirect()->route('admin.products')
                ->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat pesanan. Silakan gunakan tombol "Edit" untuk mengubah status menjadi Nonaktif.');
        }

        // Jika belum pernah dipesan, aman untuk dihapus
        if ($product->image_url) {
            Storage::disk('public')->delete($product->image_url);
        }

        $product->delete();

        return redirect()->route('admin.products')
            ->with('success', 'Produk berhasil dihapus.');
    }

    // ==================== PROMOS ====================
    public function promos()
    {
        $promos = Promo::orderBy('created_at', 'desc')->get();

        return view('admin.promos.index', compact('promos'));
    }

    public function storePromo(Request $request)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promos,code',
            'discount_type' => 'required|in:FIXED,PERCENT',
            'discount_amount' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'valid_until' => 'required|date',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Promo::create($validated);

        return redirect()->route('admin.promos')
            ->with('success', 'Promo berhasil ditambahkan.');
    }

    public function updatePromo(Request $request, Promo $promo)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promos,code,' . $promo->id,
            'discount_type' => 'required|in:FIXED,PERCENT',
            'discount_amount' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'valid_until' => 'required|date',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $promo->update($validated);

        return redirect()->route('admin.promos')
            ->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroyPromo(Promo $promo)
    {
        $promo->delete();

        return redirect()->route('admin.promos')
            ->with('success', 'Promo berhasil dihapus.');
    }

    // ==================== CONTACT & OUTLET ====================
    // ✅ TAMBAHAN BARU: Method untuk menampilkan halaman kontak
    public function contact()
    {
        $settings = Setting::firstOrCreate(
            ['key' => 'outlet_info'],
            ['value' => [
                'business_name' => 'Dapur Th Sant — Dimsum & Kuliner UMKM',
                'tagline' => 'Dimsum Kukus & Goreng Fresh Halal Tiap Hari Tanpa Pengawet',
                'whatsapp' => '081234567890',
                'hotline' => '081234567890',
                'wa_template' => 'Halo Admin Dapur Th Sant, saya ingin pesan/tanya seputar menu dimsum kukus & frozen pack...',
                'email' => 'kontak@dapurthsant.com',
                'address' => 'Jl. Ciumbuleuit No. 88, Hegarmanah, Kec. Cidadap, Kota Bandung, Jawa Barat 40141',
                'landmark' => '150m utara Kampus Unpar, seberang mini market kuning',
                'latitude' => '-6.879',
                'longitude' => '107.605',
                'operational_days' => 'Selasa - Minggu',
                'opening_hours' => '09:00 WIB',
                'closing_hours' => '21:00 WIB',
                'offline_days' => 'Senin (Khusus Pesanan Katering & PO Terjadwal)',
                'instagram' => 'dapurthsant.dimsum',
                'tiktok' => 'dapurthsant_official',
                'link_aggregator' => 'https://linktr.ee/dapurthsant',
            ]]
        );

        $data = is_array($settings->value) ? $settings->value : json_decode($settings->value, true);

        return view('admin.contact', compact('data'));
    }

    // ✅ TAMBAHAN BARU: Method untuk menyimpan perubahan kontak
    public function updateContact(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'hotline' => 'nullable|string|max:20',
            'wa_template' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'landmark' => 'nullable|string',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'operational_days' => 'nullable|string|max:100',
            'opening_hours' => 'nullable|string|max:50',
            'closing_hours' => 'nullable|string|max:50',
            'offline_days' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
            'link_aggregator' => 'nullable|url|max:255',
        ]);

        $settings = Setting::firstOrCreate(['key' => 'outlet_info']);
        $settings->update(['value' => $validated]);

        return back()->with('success', 'Informasi kontak & outlet berhasil diperbarui secara realtime!');
    }
}
