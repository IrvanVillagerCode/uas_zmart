<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display the cart contents.
     */
    public function index()
    {
        $cartItems = [];
        $total = 0;

        if (Auth::check()) {
            $dbItems = Cart::where('user_id', Auth::id())->with('product')->get();
            foreach ($dbItems as $item) {
                if ($item->product) {
                    $subtotal = $item->product->price * $item->quantity;
                    $total += $subtotal;
                    $cartItems[] = [
                        'id' => $item->id, // DB cart item id
                        'product_id' => $item->product_id,
                        'name' => $item->product->name,
                        'image' => $item->product->image,
                        'price' => $item->product->price,
                        'quantity' => $item->quantity,
                        'subtotal' => $subtotal,
                        'stock' => $item->product->stock
                    ];
                }
            }
        } else {
            $sessionCart = session()->get('cart', []);
            foreach ($sessionCart as $productId => $qty) {
                $product = Product::find($productId);
                if ($product) {
                    $subtotal = $product->price * $qty;
                    $total += $subtotal;
                    $cartItems[] = [
                        'id' => $productId, // For guests, we use product_id as the item id
                        'product_id' => $productId,
                        'name' => $product->name,
                        'image' => $product->image,
                        'price' => $product->price,
                        'quantity' => $qty,
                        'subtotal' => $subtotal,
                        'stock' => $product->stock
                    ];
                }
            }
        }

        return view('cart', compact('cartItems', 'total'));
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $productId = $request->product_id;
        $qty = $request->quantity ?? 1;

        $product = Product::findOrFail($productId);

        // Check stock
        if ($product->stock < $qty) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi!');
        }

        if (Auth::check()) {
            // Logged in: use DB
            $cartItem = Cart::where('user_id', Auth::id())
                            ->where('product_id', $productId)
                            ->first();

            if ($cartItem) {
                $newQty = $cartItem->quantity + $qty;
                if ($product->stock < $newQty) {
                    return redirect()->back()->with('error', 'Stok tidak mencukupi untuk jumlah ini!');
                }
                $cartItem->quantity = $newQty;
                $cartItem->save();
            } else {
                Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                    'quantity' => $qty
                ]);
            }
        } else {
            // Guest: use Session
            $cart = session()->get('cart', []);

            if (isset($cart[$productId])) {
                $newQty = $cart[$productId] + $qty;
                if ($product->stock < $newQty) {
                    return redirect()->back()->with('error', 'Stok tidak mencukupi untuk jumlah ini!');
                }
                $cart[$productId] = $newQty;
            } else {
                $cart[$productId] = $qty;
            }

            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    /**
     * Update product quantity in cart.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $qty = $request->quantity;

        if (Auth::check()) {
            // $id is Cart model PK id
            $cartItem = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $product = Product::findOrFail($cartItem->product_id);

            if ($product->stock < $qty) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi!');
            }

            $cartItem->quantity = $qty;
            $cartItem->save();
        } else {
            // $id is product_id for guest
            $cart = session()->get('cart', []);
            $product = Product::findOrFail($id);

            if ($product->stock < $qty) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi!');
            }

            if (isset($cart[$id])) {
                $cart[$id] = $qty;
                session()->put('cart', $cart);
            }
        }

        return redirect()->back()->with('success', 'Keranjang berhasil diperbarui!');
    }

    /**
     * Remove product from cart.
     */
    public function remove(Request $request, $id)
    {
        if (Auth::check()) {
            // $id is Cart model PK id
            $cartItem = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $cartItem->delete();
        } else {
            // $id is product_id for guest
            $cart = session()->get('cart', []);
            if (isset($cart[$id])) {
                unset($cart[$id]);
                session()->put('cart', $cart);
            }
        }

        return redirect()->back()->with('success', 'Produk berhasil dihapus dari keranjang!');
    }

    /**
     * Merge session cart to database cart upon login.
     */
    public static function syncSessionCartToDb($userId)
    {
        $sessionCart = session()->get('cart', []);
        if (empty($sessionCart)) {
            return;
        }

        foreach ($sessionCart as $productId => $qty) {
            $cartItem = Cart::where('user_id', $userId)
                            ->where('product_id', $productId)
                            ->first();

            $product = Product::find($productId);
            if (!$product) continue;

            if ($cartItem) {
                // Combine quantity, cap at stock
                $newQty = $cartItem->quantity + $qty;
                $cartItem->quantity = min($newQty, $product->stock);
                $cartItem->save();
            } else {
                Cart::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => min($qty, $product->stock)
                ]);
            }
        }

        // Clear session cart
        session()->forget('cart');
    }
}
