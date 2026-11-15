<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== USERS =====
        // Jangan pernah membuat akun dengan sandi default di produksi.
        if (app()->environment('production')) {
            $this->command?->warn('Akun default dilewati di produksi. Buat akun admin/owner secara manual.');
        } else {
            User::create([
                'name' => 'Admin umkm',
                'email' => 'admin@umkm.com',
                'password_hash' => Hash::make('password'),
                'role' => 'ADMIN',
                'phone_number' => '081234567890',
            ]);

            User::create([
                'name' => 'Owner umkm',
                'email' => 'owner@umkm.com',
                'password_hash' => Hash::make('password'),
                'role' => 'OWNER',
                'phone_number' => '081234567891',
            ]);
        }

        // ===== CATEGORIES =====
        $makanan = Category::create(['name' => 'Makanan', 'slug' => 'makanan']);
        $minuman = Category::create(['name' => 'Minuman', 'slug' => 'minuman']);
        $snack = Category::create(['name' => 'Snack', 'slug' => 'snack']);

        // ===== PRODUCTS =====
        $products = [
            ['category_id' => $makanan->id, 'name' => 'Nasi Goreng Spesial', 'price' => 25000, 'stock' => 50, 'description' => 'Nasi goreng dengan telur dan ayam'],
            ['category_id' => $makanan->id, 'name' => 'Mie Goreng', 'price' => 22000, 'stock' => 40, 'description' => 'Mie goreng dengan sayuran'],
            ['category_id' => $makanan->id, 'name' => 'Ayam Bakar', 'price' => 35000, 'stock' => 30, 'description' => 'Ayam bakar dengan sambal'],
            ['category_id' => $minuman->id, 'name' => 'Es Teh Manis', 'price' => 8000, 'stock' => 100, 'description' => 'Teh manis dingin'],
            ['category_id' => $minuman->id, 'name' => 'Es Jeruk', 'price' => 10000, 'stock' => 80, 'description' => 'Jeruk peras segar'],
            ['category_id' => $minuman->id, 'name' => 'Kopi Susu', 'price' => 15000, 'stock' => 60, 'description' => 'Kopi dengan susu segar'],
            ['category_id' => $snack->id, 'name' => 'Kentang Goreng', 'price' => 18000, 'stock' => 45, 'description' => 'French fries crispy'],
            ['category_id' => $snack->id, 'name' => 'Pisang Goreng', 'price' => 12000, 'stock' => 55, 'description' => 'Pisang goreng crispy'],
        ];

        foreach ($products as $data) {
            Product::create(array_merge($data, [
                'slug' => Str::slug($data['name']),
                'is_active' => true,
            ]));
        }

        // ===== PROMOS =====
        Promo::create([
            'code' => 'DISKON10',
            'discount_type' => 'PERCENT',
            'discount_amount' => 10,
            'min_order_amount' => 50000,
            'is_active' => true,
            'valid_until' => '2026-12-31',
        ]);

        Promo::create([
            'code' => 'POTONGAN5K',
            'discount_type' => 'FIXED',
            'discount_amount' => 5000,
            'min_order_amount' => 30000,
            'is_active' => true,
            'valid_until' => '2026-12-31',
        ]);
    }
}
