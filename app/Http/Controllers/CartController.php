<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $cartItems = [];
        $total = 0;

        foreach ($cart as $id => $item) {
            $product = Product::find($id);
            if ($product) {
                $item['product'] = $product;
                $item['subtotal'] = $product->price * $item['quantity'];
                $total += $item['subtotal'];
                $cartItems[] = $item;
            }
        }

        return view('cart.index', compact('cartItems', 'total'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if (! $product->is_active || $product->stock < $validated['quantity']) {
            return back()->with('error', 'Produk tidak tersedia atau stok tidak mencukupi.');
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $newQty = $cart[$product->id]['quantity'] + $validated['quantity'];
            if ($product->stock < $newQty) {
                return back()->with('error', 'Stok tidak mencukupi.');
            }
            $cart[$product->id]['quantity'] = $newQty;
        } else {
            $cart[$product->id] = [
                'quantity' => $validated['quantity'],
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $product = Product::find($id);
            if ($product && $product->stock >= $validated['quantity']) {
                $cart[$id]['quantity'] = $validated['quantity'];
                session()->put('cart', $cart);

                return back()->with('success', 'Keranjang diperbarui.');
            }

            return back()->with('error', 'Stok tidak mencukupi.');
        }

        return back()->with('error', 'Item tidak ditemukan.');
    }

    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);

            return back()->with('success', 'Item dihapus dari keranjang.');
        }

        return back()->with('error', 'Item tidak ditemukan.');
    }

    public function clear()
    {
        session()->forget('cart');

        return back()->with('success', 'Keranjang dikosongkan.');
    }

    public function count()
    {
        $cart = session()->get('cart', []);
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }

        return response()->json(['count' => $count]);
    }
}
