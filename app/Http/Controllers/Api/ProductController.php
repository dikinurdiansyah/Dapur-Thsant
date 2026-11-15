<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Menampilkan daftar produk
    public function index(Request $request)
    {
        // Mengambil produk yang aktif dan menyertakan data kategorinya
        $products = Product::with('category')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    // Menampilkan detail 1 produk berdasarkan slug
    public function show($slug)
    {
        $product = Product::with('category')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }
}
