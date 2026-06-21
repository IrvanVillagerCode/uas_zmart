<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        if ($order->payment_method === 'Midtrans') {
            $this->generateMidtransPaymentDetails($order);
            // Reload order to get payment_url
            $order->refresh();
            if ($order->payment_url) {
                return redirect()->away($order->payment_url);
            }
            return redirect()->route('user.dashboard')->with('warning', 'Pesanan berhasil dibuat, namun gagal menghubungi sistem pembayaran Midtrans. Silakan coba bayar nanti dari Dashboard Anda.');
        }

        return redirect()->route('user.dashboard')->with('success', 'Pesanan Anda berhasil dibuat! Silakan pantau status pengiriman di dashboard Anda.');
    }

    /**
     * Redirect user to Midtrans payment page for a pending order.
     */
    public function payNow($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu!');
        }

        $order = Order::with('user')->findOrFail($id);

        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak!');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('user.dashboard')->with('error', 'Pesanan ini sudah diproses atau dibatalkan.');
        }

        if ($order->payment_method !== 'Midtrans') {
            return redirect()->route('user.dashboard')->with('error', 'Metode pembayaran untuk pesanan ini bukan Midtrans.');
        }

        if (!$order->payment_url) {
            $this->generateMidtransPaymentDetails($order);
            $order->refresh();
        }

        if ($order->payment_url) {
            return redirect()->away($order->payment_url);
        }

        return redirect()->route('user.dashboard')->with('error', 'Gagal memuat halaman pembayaran Midtrans. Silakan coba beberapa saat lagi.');
    }

    /**
     * Handle Midtrans Webhook Notification callback.
     */
    public function notification(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $signatureKey = $request->input('signature_key');
        
        // Local validation of signature
        $localSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        if ($signatureKey !== $localSignature) {
            Log::warning('Midtrans Webhook: Invalid Signature Key. Order ID: ' . $orderId);
            return response()->json(['message' => 'Invalid Signature'], 400);
        }

        $order = Order::where('order_number', $orderId)->first();
        if (!$order) {
            Log::warning('Midtrans Webhook: Order not found. Order ID: ' . $orderId);
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transactionStatus = $request->input('transaction_status');
        $type = $request->input('payment_type');
        $fraudStatus = $request->input('fraud_status');

        Log::info("Midtrans Webhook received: Order ID {$orderId}, Status {$transactionStatus}, Type {$type}");

        // Map status
        if ($transactionStatus == 'capture') {
            if ($type == 'credit_card') {
                if ($fraudStatus == 'challenge') {
                    $order->status = 'pending';
                } else {
                    $order->status = 'success'; // Paid
                }
            }
        } elseif ($transactionStatus == 'settlement') {
            $order->status = 'success'; // Paid
        } elseif ($transactionStatus == 'pending') {
            $order->status = 'pending';
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $order->status = 'failed';
        } elseif ($transactionStatus == 'cancel') {
            $order->status = 'cancelled';
        }

        $order->save();

        return response()->json(['status' => 'success', 'message' => 'Notification processed successfully']);
    }

    /**
     * Generate Midtrans snap token and payment URL for an order.
     */
    private function generateMidtransPaymentDetails(Order $order): void
    {
        try {
            $serverKey = config('services.midtrans.server_key');
            $isProduction = config('services.midtrans.is_production');
            $baseUrl = $isProduction 
                ? 'https://app.midtrans.com/snap/v1/transactions' 
                : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

            $payload = [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => (int) $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $order->user->full_name ?? $order->user->username,
                    'email' => $order->user->email,
                ],
                'callbacks' => [
                    'finish' => route('user.dashboard'),
                    'unfinish' => route('user.dashboard'),
                    'error' => route('user.dashboard'),
                ]
            ];

            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->contentType('application/json')
                ->post($baseUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $order->update([
                    'snap_token' => $data['token'] ?? null,
                    'payment_url' => $data['redirect_url'] ?? null,
                ]);
            } else {
                Log::error('Midtrans API Request Failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Midtrans Exception: ' . $e->getMessage());
        }
    }
}
