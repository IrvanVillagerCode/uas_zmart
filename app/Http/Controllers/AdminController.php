<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with stats, products, and orders.
     */
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return \redirect()->route('login')->with('error', 'Akses ditolak! Halaman ini hanya untuk Admin.');
        }

        // Stats
        $totalSales = Order::whereIn('status', ['pending', 'success'])->sum('total_amount');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();

        // Products & Orders lists
        $products = Product::orderBy('id', 'desc')->get();
        $orders = Order::with('user', 'items.product')->orderBy('id', 'desc')->get();

        return \view('admin.dashboard', compact('totalSales', 'totalOrders', 'totalProducts', 'totalCustomers', 'products', 'orders'));
    }

    /**
     * Store a new product.
     */
    public function storeProduct(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return \redirect()->back()->with('error', 'Akses ditolak!');
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
            $file->move(\public_path('uploads/products'), $filename);
            $imagePath = \asset('uploads/products/' . $filename);
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

        return \redirect()->back()->with('success', 'Produk baru berhasil ditambahkan!');
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Request $request, int $id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return \redirect()->back()->with('error', 'Akses ditolak!');
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
            $file->move(\public_path('uploads/products'), $filename);
            $imagePath = \asset('uploads/products/' . $filename);
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

        return \redirect()->back()->with('success', 'Produk berhasil diperbarui!');
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(int $id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return \redirect()->back()->with('error', 'Akses ditolak!');
        }

        $product = Product::findOrFail($id);
        $product->delete();

        return \redirect()->back()->with('success', 'Produk berhasil dihapus!');
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Request $request, int $id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return \redirect()->back()->with('error', 'Akses ditolak!');
        }

        $request->validate([
            'status' => 'required|in:pending,success,failed,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return \redirect()->back()->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui menjadi " . ucfirst($request->status) . "!");
    }

    /**
     * Get active users and their real-time session duration.
     */
    public function activeUsers(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Akses ditolak!'], 403);
        }

        // Get sessions active in the last 5 minutes (300 seconds)
        $threshold = time() - 300;

        $sessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id as session_id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.username',
                'users.full_name',
                'users.email',
                'users.avatar',
                'users.role'
            )
            ->where('sessions.last_activity', '>=', $threshold)
            ->orderBy('sessions.last_activity', 'desc')
            ->get();

        $data = $sessions->map(function ($session) {
            $ua = $session->user_agent ?? 'Unknown';
            $browser = 'Unknown Browser';
            $platform = 'Unknown Platform';

            if (preg_match('/MSIE/i', $ua) && !preg_match('/Opera/i', $ua)) {
                $browser = 'Internet Explorer';
            } elseif (preg_match('/Firefox/i', $ua)) {
                $browser = 'Mozilla Firefox';
            } elseif (preg_match('/Chrome/i', $ua)) {
                $browser = 'Google Chrome';
            } elseif (preg_match('/Safari/i', $ua)) {
                $browser = 'Apple Safari';
            } elseif (preg_match('/Opera/i', $ua)) {
                $browser = 'Opera';
            }

            if (preg_match('/windows|win32/i', $ua)) {
                $platform = 'Windows';
            } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
                $platform = 'macOS';
            } elseif (preg_match('/linux/i', $ua)) {
                $platform = 'Linux';
            } elseif (preg_match('/iphone|ipad/i', $ua)) {
                $platform = 'iOS';
            } elseif (preg_match('/android/i', $ua)) {
                $platform = 'Android';
            }

            $lastActiveDiff = time() - $session->last_activity;

            return [
                'session_id' => substr($session->session_id, 0, 10) . '...',
                'user_id' => $session->user_id,
                'name' => $session->full_name ?? ($session->username ?? 'Guest/Pengunjung'),
                'email' => $session->email ?? '-',
                'avatar' => $session->avatar ?? null,
                'role' => $session->role ?? 'guest',
                'ip_address' => $session->ip_address ?? '127.0.0.1',
                'browser' => $browser,
                'platform' => $platform,
                'last_activity' => $session->last_activity,
                'last_active_diff' => $lastActiveDiff,
                'is_admin' => $session->role === 'admin',
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'active_users' => $data
        ]);
    }
}
