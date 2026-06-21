<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index()
    {
        if (!Auth::check()) {
            return \redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu!');
        }

        // Redirect admin to admin dashboard if they try to access user dashboard
        if (Auth::user()->role === 'admin') {
            return \redirect()->route('admin.dashboard');
        }

        $orders = Order::where('user_id', Auth::id())
                        ->with('items.product')
                        ->orderBy('id', 'desc')
                        ->get();

        return \view('dashboard', compact('orders'));
    }

    public function updateProfile(Request $request)
    {
        if (!Auth::check()) {
            return \redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu!');
        }

        $user = Auth::user();

        $request->validate([
            'full_name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'avatar' => 'nullable|string|max:500',
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $avatar = $user->avatar;

        if ($request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $path = \public_path('uploads/avatars');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $file->move($path, $filename);
            $avatar = '/uploads/avatars/' . $filename;
        } elseif ($request->has('avatar')) {
            $avatar = $request->avatar;
        }

        $user->update([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'avatar' => $avatar,
        ]);

        return \redirect()->back()->with('success', 'Profil Anda berhasil diperbarui!');
    }

    /**
     * Show the printable invoice.
     */
    public function showInvoice(int $id)
    {
        if (!Auth::check()) {
            return \redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu!');
        }

        $order = Order::with('user', 'items.product')->findOrFail($id);

        // Security check: Only the owner of the order or an admin can view the invoice
        if ($order->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            \abort(403, 'Akses ditolak!');
        }

        return \view('invoice', compact('order'));
    }

    /**
     * Update user password synced from Firebase.
     */
    public function updatePassword(Request $request)
    {
        if (!Auth::check()) {
            return \response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu!'
            ], 401);
        }

        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user = Auth::user();

        // Update local database password field
        $user->update([
            'password' => $request->password,
        ]);

        return \response()->json([
            'success' => true,
            'message' => 'Password berhasil disinkronkan ke database lokal.'
        ]);
    }
}
