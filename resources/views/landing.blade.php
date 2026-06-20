@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <!-- Hero Section -->
    <section class="hero animate-fade-in">
        <div class="container hero-grid">
            <div class="hero-content">
                <h1>Temukan Gaya Terbaik Anda Bersama Kami</h1>
                <p>Koleksi pakaian berkualitas premium, didesain khusus untuk menunjang penampilan harian Anda agar tetap nyaman, percaya diri, dan tampil elegan.</p>
                <div class="hero-actions">
                    <a href="#katalog" class="btn btn-accent btn-lg">Belanja Sekarang</a>
                    <a href="{{ route('register') }}" class="btn btn-secondary btn-lg">Daftar Akun</a>
                </div>
            </div>
            <div class="hero-image-wrapper">
                <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?w=800&auto=format&fit=crop&q=80" alt="Z-Mart Premium Fashion" class="hero-img">
            </div>
        </div>
    </section>

    <!-- Shop Catalog Section -->
    <section class="shop-section" id="katalog">
        <div class="section-header">
            <div class="section-title">
                <h2>Katalog Produk Pakaian</h2>
                <p>Menampilkan pakaian butik terbaik dengan bahan katun combed, corduroy, flanel, dan denim pilihan.</p>
            </div>
            
            <!-- Category Filtering -->
            <ul class="category-list">
                <li class="category-item {{ !request('category') ? 'active' : '' }}">
                    <a href="{{ route('landing') }}#katalog">Semua</a>
                </li>
                @foreach($categories as $category)
                    <li class="category-item {{ request('category') === $category ? 'active' : '' }}">
                        <a href="{{ route('landing', ['category' => $category]) }}#katalog">{{ ucfirst($category) }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Products Grid -->
        @if($products->isEmpty())
            <div style="text-align: center; padding: 60px; background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border);">
                <p style="color: var(--text-muted); font-size: 16px;">Maaf, tidak ada produk pakaian yang tersedia untuk kategori ini saat ini.</p>
            </div>
        @else
            <div class="products-grid">
                @foreach($products as $product)
                    <div class="product-card">
                        <div class="product-image-container">
                            <span class="product-category">{{ $product->category }}</span>
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="product-image">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">{{ $product->name }}</h3>
                            <p class="product-desc">{{ $product->description ?? 'Bahan premium nyaman dipakai sehari-hari.' }}</p>
                            
                            <div style="margin-bottom: 12px; font-size: 13px; font-weight: 500;">
                                @if($product->stock > 0)
                                    <span style="color: var(--success);">🟢 Tersedia (Stok: {{ $product->stock }})</span>
                                @else
                                    <span style="color: var(--danger);">🔴 Stok Habis</span>
                                @endif
                            </div>

                            <div class="product-footer">
                                <span class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                
                                <form action="{{ route('cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary btn-sm" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                        🛒 Tambah
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
