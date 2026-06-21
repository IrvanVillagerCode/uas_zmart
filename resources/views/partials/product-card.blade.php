{{--
    Partial: Product Card
    Variables:
      $product      - Product model instance
      $accentColor  - (optional) hex color for stock badge & button, default: var(--primary)
--}}
@php $accent = $accentColor ?? 'var(--primary)'; @endphp

<div class="product-card">
    <div class="product-image-container">
        {{-- Kategori badge --}}
        <span class="product-category">{{ ucfirst($product->category) }}</span>

        {{-- Badge Stok Habis --}}
        @if($product->stock <= 0)
            <span style="position:absolute; top:16px; right:16px; background:#ef4444; color:white; font-size:10px; font-weight:700; padding:3px 10px; border-radius:99px; letter-spacing:.5px;">HABIS</span>
        @elseif($product->stock <= 10)
            <span style="position:absolute; top:16px; right:16px; background:#f59e0b; color:white; font-size:10px; font-weight:700; padding:3px 10px; border-radius:99px; letter-spacing:.5px;">SEGERA HABIS</span>
        @endif

        <img src="{{ $product->image }}"
             alt="{{ $product->name }}"
             class="product-image"
             loading="lazy"
             onerror="this.src='https://placehold.co/400x500/f1f5f9/94a3b8?text=No+Image'">
    </div>

    <div class="product-info">
        <h3 class="product-title">{{ $product->name }}</h3>
        <p class="product-desc">{{ $product->description ?? 'Produk berkualitas pilihan Z-MART.' }}</p>

        {{-- Stock indicator --}}
        <div style="display:flex; align-items:center; gap:6px; margin-bottom:12px; font-size:12px; font-weight:600;">
            @if($product->stock > 10)
                <span style="width:7px; height:7px; border-radius:50%; background:#10b981; display:inline-block; flex-shrink:0;"></span>
                <span style="color:#10b981;">Tersedia · Stok {{ $product->stock }}</span>
            @elseif($product->stock > 0)
                <span style="width:7px; height:7px; border-radius:50%; background:#f59e0b; display:inline-block; flex-shrink:0;"></span>
                <span style="color:#f59e0b;">Stok terbatas · {{ $product->stock }} tersisa</span>
            @else
                <span style="width:7px; height:7px; border-radius:50%; background:#ef4444; display:inline-block; flex-shrink:0;"></span>
                <span style="color:#ef4444;">Stok habis</span>
            @endif
        </div>

        <div class="product-footer">
            <div>
                <span class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
            </div>

            @if($product->stock > 0)
                <form action="{{ route('cart.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-sm"
                        style="background:{{ $accent }}; color:white; border:none; padding:8px 16px; border-radius:var(--radius-md); font-size:13px; font-weight:600; cursor:pointer; transition:var(--transition); white-space:nowrap;"
                        onmouseover="this.style.opacity='0.85'"
                        onmouseout="this.style.opacity='1'">
                        🛒 Tambah
                    </button>
                </form>
            @else
                <button disabled class="btn btn-sm"
                    style="background:var(--border); color:var(--text-muted); border:none; padding:8px 16px; border-radius:var(--radius-md); font-size:13px; font-weight:600; cursor:not-allowed;">
                    Habis
                </button>
            @endif
        </div>
    </div>
</div>
