@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
    <div class="cart-container">
        <h2 style="font-size: 32px; margin-bottom: 24px; letter-spacing: -0.5px;">Keranjang Belanja Anda</h2>

        @if(empty($cartItems))
            <div style="text-align: center; padding: 60px; background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-md);">
                <span style="font-size: 48px; display: block; margin-bottom: 16px;">🛒</span>
                <h3 style="font-size: 20px; margin-bottom: 8px;">Keranjang Anda Kosong</h3>
                <p style="color: var(--text-muted); margin-bottom: 24px;">Silakan lihat katalog pakaian kami untuk menambahkan produk baju baru ke keranjang belanja Anda.</p>
                <a href="{{ route('landing') }}" class="btn btn-primary">Lihat Produk Pakaian</a>
            </div>
        @else
            <div class="cart-layout">
                <!-- Cart Items List -->
                <div class="cart-table-card animate-fade-in">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Kuantitas</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cartItems as $item)
                                <tr>
                                    <td>
                                        <div class="cart-product">
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="cart-product-img">
                                            <div>
                                                <span class="cart-product-name">{{ $item['name'] }}</span>
                                                <span style="display: block; font-size: 11px; color: var(--text-muted); margin-top: 4px;">Kategori: Pakaian</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-weight: 500; font-size: 15px;">
                                        Rp {{ number_format($item['price'], 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <!-- Quantity Control Form -->
                                        <form action="{{ route('cart.update', $item['id']) }}" method="POST" class="qty-control" id="qty-form-{{ $item['id'] }}">
                                            @csrf
                                            <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" class="qty-btn" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>-</button>
                                            <input type="text" readonly value="{{ $item['quantity'] }}" class="qty-input">
                                            <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="qty-btn" {{ $item['quantity'] >= $item['stock'] ? 'disabled' : '' }}>+</button>
                                        </form>
                                    </td>
                                    <td style="font-weight: 700; font-size: 15px; color: var(--secondary);">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <!-- Remove Item Form -->
                                        <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk ini dari keranjang?')">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Order Summary Panel -->
                <div class="cart-summary-card animate-fade-in" style="animation-delay: 0.1s;">
                    <h3 style="font-size: 20px; margin-bottom: 20px; border-bottom: 1.5px solid var(--border); padding-bottom: 12px;">Ringkasan Belanja</h3>
                    
                    <div class="summary-row">
                        <span style="color: var(--text-muted);">Total Barang</span>
                        <span style="font-weight: 600;">{{ array_sum(array_column($cartItems, 'quantity')) }} pcs</span>
                    </div>
                    
                    <div class="summary-row">
                        <span style="color: var(--text-muted);">Pengiriman</span>
                        <span style="color: var(--success); font-weight: 600;">GRATIS</span>
                    </div>

                    <div class="summary-row total">
                        <span>Total Tagihan</span>
                        <span style="color: var(--primary);">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <div style="margin-top: 30px;">
                        @auth
                            <a href="{{ route('checkout.index') }}" class="btn btn-accent btn-lg" style="width: 100%;">Lanjut ke Checkout</a>
                        @else
                            <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-md); padding: 12px; font-size: 13px; color: #92400e; margin-bottom: 16px; text-align: center; font-weight: 500;">
                                🔐 Anda harus login terlebih dahulu sebelum bisa melakukan checkout.
                            </div>
                            <a href="{{ route('checkout.index') }}" class="btn btn-accent btn-lg" style="width: 100%;">Login & Checkout</a>
                        @endauth
                        
                        <a href="{{ route('landing') }}" class="btn btn-secondary btn-lg" style="width: 100%; margin-top: 12px;">Kembali Belanja</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
