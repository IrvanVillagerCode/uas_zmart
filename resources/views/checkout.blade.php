@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="cart-container">
        <h2 style="font-size: 32px; margin-bottom: 24px; letter-spacing: -0.5px;">Checkout Pesanan</h2>

        <div class="cart-layout animate-fade-in">
            <!-- Checkout Form Card -->
            <div class="cart-table-card" style="padding: 30px;">
                <h3 style="font-size: 20px; margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 12px; color: var(--secondary);">Informasi Pengiriman & Pembayaran</h3>
                
                <form action="{{ route('checkout.store') }}" method="POST">
                    @csrf
                    
                    <!-- Customer Information (Read-only) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label class="form-label">Nama Pemesan</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->full_name }}" disabled style="background-color: var(--surface-muted); color: var(--text-muted);">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Pemesan</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->email }}" disabled style="background-color: var(--surface-muted); color: var(--text-muted);">
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="form-group">
                        <label class="form-label" for="shipping_address">Alamat Lengkap Pengiriman *</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="4" placeholder="Masukkan nama jalan, nomor rumah, RT/RW, kecamatan, kota, provinsi, dan kode pos" required minlength="10">{{ old('shipping_address') }}</textarea>
                        @error('shipping_address')
                            <span style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Payment Method -->
                    <div class="form-group">
                        <label class="form-label" for="payment_method">Metode Pembayaran *</label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="Midtrans" {{ old('payment_method') === 'Midtrans' ? 'selected' : '' }}>Midtrans (Virtual Account, QRIS, E-Wallet, Kartu Kredit)</option>
                            <option value="Transfer Bank" {{ old('payment_method') === 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank Manual (BCA / Mandiri)</option>
                            <option value="COD" {{ old('payment_method') === 'COD' ? 'selected' : '' }}>COD (Bayar di Tempat saat Barang Tiba)</option>
                        </select>
                        @error('payment_method')
                            <span style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Additional Notes -->
                    <div class="form-group">
                        <label class="form-label" for="notes">Catatan Tambahan (Opsional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Contoh: Titipkan di pos satpam jika tidak di rumah">{{ old('notes') }}</textarea>
                    </div>

                    <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 20px;">
                        <a href="{{ route('cart.index') }}" class="btn btn-secondary">Kembali ke Keranjang</a>
                        <button type="submit" class="btn btn-accent btn-lg">Konfirmasi & Buat Pesanan</button>
                    </div>
                </form>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="cart-summary-card">
                <h3 style="font-size: 20px; margin-bottom: 20px; border-bottom: 1.5px solid var(--border); padding-bottom: 12px; color: var(--secondary);">Rincian Pesanan</h3>
                
                <div style="max-height: 250px; overflow-y: auto; margin-bottom: 20px; padding-right: 8px;">
                    @foreach($cartItems as $item)
                        @if($item->product)
                            <div style="display: flex; gap: 12px; margin-bottom: 16px; align-items: center; border-bottom: 1px solid #f8fafc; padding-bottom: 10px;">
                                <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}" style="width: 44px; height: 55px; object-fit: cover; border-radius: var(--radius-sm);">
                                <div style="flex-grow: 1; min-width: 0;">
                                    <h4 style="font-size: 13px; font-weight: 600; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; color: var(--secondary);">{{ $item->product->name }}</h4>
                                    <span style="font-size: 12px; color: var(--text-muted);">{{ $item->quantity }} x Rp {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                </div>
                                <span style="font-size: 13px; font-weight: 700; color: var(--secondary); margin-left: auto;">
                                    Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="summary-row">
                    <span style="color: var(--text-muted);">Total Kuantitas</span>
                    <span style="font-weight: 600;">{{ $cartItems->sum('quantity') }} pcs</span>
                </div>
                
                <div class="summary-row">
                    <span style="color: var(--text-muted);">Biaya Pengiriman</span>
                    <span style="color: var(--success); font-weight: 600;">GRATIS</span>
                </div>

                <div class="summary-row total" style="border-top: 1.5px solid var(--border); padding-top: 18px;">
                    <span>Total Tagihan</span>
                    <span style="color: var(--primary);">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                
                <div style="margin-top: 20px; font-size: 11px; color: var(--text-muted); background-color: var(--surface-muted); padding: 12px; border-radius: var(--radius-md); text-align: center;">
                    🔒 Pembayaran Anda aman bersama Z-MART. Pesanan akan segera dikirimkan setelah status pembayaran diverifikasi.
                </div>
            </div>
        </div>
    </div>
@endsection
