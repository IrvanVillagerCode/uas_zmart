<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with stats, products, and orders.
     */
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('login')->with('error', 'Akses ditolak! Halaman ini hanya untuk Admin.');
        }

        // Stats
        $totalSales = Order::whereIn('status', ['pending', 'success'])->sum('total_amount');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();

        // Products & Orders lists
        $products = Product::orderBy('id', 'desc')->get();
        $orders = Order::with('user', 'items.product')->orderBy('id', 'desc')->get();

        return view('admin.dashboard', compact('totalSales', 'totalOrders', 'totalProducts', 'totalCustomers', 'products', 'orders'));
    }

    /**
     * Store a new product.
     */
    public function storeProduct(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_url' => 'nullable|string|url',
            'category' => 'required|string|max:100',
        ]);

        $imagePath = 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=600&auto=format&fit=crop&q=80'; // Default placeholder

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = asset('uploads/products/' . $filename);
        } elseif ($request->image_url) {
            $imagePath = $request->image_url;
        }

        Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
            'external_link' => '',
            'category' => strtolower($request->category),
            'is_active' => 1
        ]);

        return redirect()->back()->with('success', 'Produk baru berhasil ditambahkan!');
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_url' => 'nullable|string|url',
            'category' => 'required|string|max:100',
        ]);

        $imagePath = $product->image;

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = asset('uploads/products/' . $filename);
        } elseif ($request->image_url) {
            $imagePath = $request->image_url;
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
            'category' => strtolower($request->category),
        ]);

        return redirect()->back()->with('success', 'Produk berhasil diperbarui!');
    }

    /**
     * Delete a product.
     */
    public function deleteProduct($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus!');
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $request->validate([
            'status' => 'required|in:pending,success,failed,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return redirect()->back()->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui menjadi " . ucfirst($request->status) . "!");
    }
}
