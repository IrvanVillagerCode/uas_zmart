<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Display the checkout form.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu untuk melakukan checkout!');
        }

        $cartItems = Cart::where('user_id', Auth::id())->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja Anda kosong!');
        }

        $total = 0;
        foreach ($cartItems as $item) {
            if ($item->product) {
                $total += $item->product->price * $item->quantity;
            }
        }

        return view('checkout', compact('cartItems', 'total'));
    }

    /**
     * Process the order.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu!');
        }

        $request->validate([
            'shipping_address' => 'required|string|min:10',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $cartItems = Cart::where('user_id', Auth::id())->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong!');
        }

        // Validate stock and calculate total
        $total = 0;
        foreach ($cartItems as $item) {
            if (!$item->product) {
                return redirect()->route('cart.index')->with('error', 'Salah satu produk tidak ditemukan.');
            }
            if ($item->product->stock < $item->quantity) {
                return redirect()->route('cart.index')->with('error', "Stok untuk produk '{$item->product->name}' tidak mencukupi!");
            }
            $total += $item->product->price * $item->quantity;
        }

        // Create Order
        $order = Order::create([
            'user_id' => Auth::id(),
            'order_number' => 'ORD-' . time() . rand(10, 99),
            'total_amount' => $total,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'shipping_address' => $request->shipping_address,
            'notes' => $request->notes,
        ]);

        // Create Order Items and update stock
        foreach ($cartItems as $item) {
            $product = $item->product;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item->quantity,
                'unit_price' => $product->price,
                'subtotal' => $product->price * $item->quantity,
            ]);

            // Decrement Stock
            $product->stock -= $item->quantity;
            $product->save();
        }

        // Clear Cart
        Cart::where('user_id', Auth::id())->delete();

        return redirect()->route('user.dashboard')->with('success', 'Pesanan Anda berhasil dibuat! Silakan pantau status pengiriman di dashboard Anda.');
    }
}
