@extends('layouts.app')

@section('title', 'Beranda')

@section('styles')
<style>
/* ─── Hero ──────────────────────────────────────────────────────────────────── */
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(79,70,229,0.10);
    color: var(--primary);
    border: 1px solid rgba(79,70,229,0.25);
    padding: 6px 16px;
    border-radius: var(--radius-full);
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 20px;
}
.hero-stats { display:flex; gap:32px; margin-top:28px; padding-top:24px; border-top:1px solid var(--border); }
.hero-stat-number { display:block; font-family:var(--font-heading); font-size:26px; font-weight:800; color:var(--secondary); }
.hero-stat-label  { font-size:12px; color:var(--text-muted); font-weight:500; }

/* ─── Section Badge ─────────────────────────────────────────────────────────── */
.section-badge {
    display: inline-block;
    color: white;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: var(--radius-full);
    margin-bottom: 12px;
}

/* ─── Category Tabs ─────────────────────────────────────────────────────────── */
.cat-tabs { display:flex; gap:10px; flex-wrap:wrap; list-style:none; padding:0; margin:0; }
.cat-tab-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 9px 20px;
    border-radius: var(--radius-full);
    background: var(--surface);
    border: 1.5px solid var(--border);
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    white-space: nowrap;
    transition: var(--transition);
    font-family: var(--font-body);
}
.cat-tab-btn:hover  { border-color: #059669; color:#059669; }
.cat-tab-btn.active { background:#059669; border-color:#059669; color:white; }

/* Fashion tabs */
.fashion-tab-btn { background:var(--surface); border:1.5px solid var(--border); font-size:13px; font-weight:600; color:var(--text-muted); }
.fashion-tab-btn:hover  { border-color:var(--primary); color:var(--primary); }
.fashion-tab-btn.active { background:var(--primary); border-color:var(--primary); color:white; }

/* ─── Product Card ──────────────────────────────────────────────────────────── */
.product-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    border: 1px solid rgba(15,23,42,0.05);
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: var(--transition);
    cursor: pointer;
    position: relative;
}
.product-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-xl); }
.product-card:hover .product-image { transform: scale(1.06); }

/* ─── Product Modal ─────────────────────────────────────────────────────────── */
.modal-backdrop {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(10,10,20,0.70);
    backdrop-filter: blur(8px);
    display: flex; align-items: center; justify-content: center;
    padding: 16px;
    opacity: 0; pointer-events: none;
    transition: opacity 0.3s ease;
}
.modal-backdrop.open { opacity:1; pointer-events:all; }

.modal-box {
    background: var(--surface);
    border-radius: 20px;
    box-shadow: 0 40px 100px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 820px;
    max-height: 92vh;
    overflow: hidden;
    display: flex;
    flex-direction: row;
    transform: translateY(24px) scale(0.96);
    transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s ease;
    opacity: 0;
    position: relative;
}
.modal-backdrop.open .modal-box {
    transform: translateY(0) scale(1);
    opacity: 1;
}

/* Kolom Kiri — Gambar */
.modal-img-wrap {
    flex: 0 0 42%;
    min-height: 400px;
    position: relative;
    overflow: hidden;
    background: var(--surface-muted);
    border-radius: 20px 0 0 20px;
}
.modal-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.modal-cat-badge {
    position: absolute;
    top: 16px; left: 16px;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(6px);
    font-size: 11px; font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    padding: 5px 14px;
    border-radius: 99px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Kolom Kanan — Konten */
.modal-body {
    flex: 1;
    padding: 32px 36px;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    position: relative;
}

.modal-close {
    position: absolute;
    top: 14px; right: 16px;
    width: 34px; height: 34px;
    background: var(--surface-muted);
    border: 1.5px solid var(--border);
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    color: var(--text-muted);
    display: flex; align-items: center; justify-content: center;
    transition: var(--transition);
    line-height: 1;
    z-index: 10;
}
.modal-close:hover { background: #fee2e2; border-color: #fca5a5; color: #ef4444; }

.modal-price {
    font-family: var(--font-heading);
    font-size: 30px;
    font-weight: 800;
    color: var(--secondary);
    margin: 10px 0 6px;
}
.modal-desc {
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.8;
    flex-grow: 1;
    margin-bottom: 4px;
}
.modal-stock-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    margin: 14px 0 18px;
}
.stock-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }

.modal-add-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px;
    border-radius: var(--radius-md);
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: var(--transition);
    margin-top: 12px;
}
.modal-add-btn:not(:disabled):hover { opacity:.88; transform:translateY(-1px); }
.modal-add-btn:disabled { cursor: not-allowed; }

/* Responsive */
@media (max-width: 650px) {
    .modal-box { flex-direction: column; max-height: 95vh; }
    .modal-img-wrap { flex: 0 0 220px; min-height: 220px; border-radius: 20px 20px 0 0; }
    .modal-body { padding: 20px 22px; }
    .modal-price { font-size: 24px; }
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--surface);
    border-radius: var(--radius-lg);
    border: 1px dashed var(--border);
    color: var(--text-muted);
}
</style>
@endsection

@section('content')

{{-- ─── HERO ──────────────────────────────────────────────────────────────────── --}}
<section class="hero animate-fade-in">
    <div class="container hero-grid">
        <div class="hero-content">
            <div class="hero-badge">🛍️ Z-MART Boutique &amp; Swalayan</div>
            <h1>Belanja Lengkap,<br>Satu Tempat</h1>
            <p>Temukan koleksi fashion premium dan kebutuhan harian berkualitas. Harga terjangkau, pilihan lengkap — semua ada di Z-MART.</p>
            <div class="hero-actions">
                <a href="#katalog-fashion" class="btn btn-accent btn-lg">👗 Fashion</a>
                <a href="#katalog-harian" class="btn btn-secondary btn-lg">🛒 Kebutuhan Harian</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-number">{{ $fashionProducts->count() + $dailyProducts->count() }}</span>
                    <span class="hero-stat-label">Produk</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-number">{{ $fashionCategoryList->count() + $dailyCategoryList->count() }}</span>
                    <span class="hero-stat-label">Kategori</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-number">24/7</span>
                    <span class="hero-stat-label">Online</span>
                </div>
            </div>
        </div>
        <div class="hero-image-wrapper">
            <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=800&auto=format&fit=crop&q=80" alt="Z-Mart Shopping" class="hero-img">
        </div>
    </div>
</section>

{{-- ─── FEATURE BAR ─────────────────────────────────────────────────────────── --}}
<section style="padding:40px 0; background:linear-gradient(135deg,var(--secondary) 0%,#1e293b 100%);">
    <div class="container">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:24px;">
            @foreach([['🚚','Pengiriman Cepat','Estimasi 1-3 hari sampai'],['💎','Produk Original','Garansi keaslian 100%'],['🔄','Mudah Dikembalikan','Retur dalam 7 hari'],['🔒','Pembayaran Aman','Dilindungi enkripsi SSL']] as $f)
            <div style="display:flex;align-items:center;gap:14px;color:white;">
                <span style="font-size:28px;flex-shrink:0;">{{ $f[0] }}</span>
                <div>
                    <div style="font-weight:700;font-size:14px;font-family:var(--font-heading);">{{ $f[1] }}</div>
                    <div style="font-size:12px;color:rgba(255,255,255,0.6);margin-top:2px;">{{ $f[2] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ─── KATALOG FASHION ─────────────────────────────────────────────────────── --}}
<section class="shop-section" id="katalog-fashion">
    <div class="container">
        <div class="section-header">
            <div class="section-title">
                <div class="section-badge" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">👗 FASHION</div>
                <h2>Koleksi Pakaian Premium</h2>
                <p>Pilihan fashion terkini dengan bahan cotton combed, denim, flanel, dan corduroy berkualitas.</p>
            </div>
            {{-- Filter JS --}}
            <ul class="cat-tabs" id="fashion-tabs">
                <li><button class="cat-tab-btn fashion-tab-btn active" data-filter="all" onclick="filterFashion('all',this)">Semua</button></li>
                @foreach($fashionCategoryList as $cat)
                <li><button class="cat-tab-btn fashion-tab-btn" data-filter="{{ $cat }}" onclick="filterFashion('{{ $cat }}',this)">{{ ucfirst($cat) }}</button></li>
                @endforeach
            </ul>
        </div>

        <div class="products-grid" id="fashion-grid">
            @foreach($fashionProducts as $product)
            <div class="product-card fashion-card" data-cat="{{ $product->category }}"
                 onclick="openModal({{ $product->id }})">
                <div class="product-image-container">
                    <span class="product-category">{{ ucfirst($product->category) }}</span>
                    @if($product->stock <= 0)
                        <span style="position:absolute;top:16px;right:16px;background:#ef4444;color:white;font-size:10px;font-weight:700;padding:3px 10px;border-radius:99px;">HABIS</span>
                    @elseif($product->stock <= 10)
                        <span style="position:absolute;top:16px;right:16px;background:#f59e0b;color:white;font-size:10px;font-weight:700;padding:3px 10px;border-radius:99px;">HAMPIR HABIS</span>
                    @endif
                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="product-image" loading="lazy"
                         onerror="this.src='https://placehold.co/400x500/e0e7ff/4f46e5?text={{ urlencode($product->category) }}'">
                </div>
                <div class="product-info">
                    <h3 class="product-title">{{ $product->name }}</h3>
                    <p class="product-desc">{{ $product->description ?? 'Klik untuk lihat detail produk.' }}</p>
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;font-size:12px;font-weight:600;">
                        @if($product->stock > 10)
                            <span style="width:7px;height:7px;border-radius:50%;background:#10b981;display:inline-block;"></span>
                            <span style="color:#10b981;">Tersedia · {{ $product->stock }}</span>
                        @elseif($product->stock > 0)
                            <span style="width:7px;height:7px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                            <span style="color:#f59e0b;">Hampir habis · {{ $product->stock }} sisa</span>
                        @else
                            <span style="width:7px;height:7px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
                            <span style="color:#ef4444;">Stok habis</span>
                        @endif
                    </div>
                    <div class="product-footer">
                        <span class="product-price">Rp {{ number_format($product->price,0,',','.') }}</span>
                        <span style="font-size:12px;color:var(--primary);font-weight:600;">Lihat Detail →</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div id="fashion-empty" class="empty-state" style="display:none;">
            <div style="font-size:48px;margin-bottom:16px;">👗</div>
            <p>Tidak ada produk untuk kategori ini.</p>
        </div>
    </div>
</section>

{{-- ─── DIVIDER ─────────────────────────────────────────────────────────────── --}}
<div style="background:var(--surface-muted);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:52px 0;">
    <div class="container" style="text-align:center;">
        <div style="font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--text-muted);margin-bottom:12px;">Juga Tersedia Di Z-MART</div>
        <h2 style="font-size:30px;color:var(--secondary);">Kebutuhan Harian Anda</h2>
        <p style="color:var(--text-muted);margin-top:8px;max-width:480px;margin-left:auto;margin-right:auto;">Dari sembako, makanan, minuman, hingga produk kebersihan dan perawatan — tersedia lengkap.</p>
    </div>
</div>

{{-- ─── KATALOG KEBUTUHAN HARIAN ────────────────────────────────────────────── --}}
<section class="shop-section" id="katalog-harian" style="background:var(--surface-muted);">
    <div class="container">
        <div class="section-header">
            <div class="section-title">
                <div class="section-badge" style="background:linear-gradient(135deg,#059669,#10b981);">🛒 KEBUTUHAN HARIAN</div>
                <h2>Belanja Kebutuhan Pokok</h2>
                <p>Sembako, makanan &amp; minuman, produk kebersihan, dan perawatan diri — pilihan lengkap.</p>
            </div>

            {{-- Filter Tabs JS (Instant, Tanpa Reload) --}}
            @php
                $catConfig = [
                    'sembako'    => ['icon' => '🌾', 'label' => 'Sembako'],
                    'makanan'    => ['icon' => '🍜', 'label' => 'Makanan'],
                    'minuman'    => ['icon' => '🥤', 'label' => 'Minuman'],
                    'kebersihan' => ['icon' => '🧹', 'label' => 'Kebersihan'],
                    'perawatan'  => ['icon' => '🧴', 'label' => 'Perawatan'],
                ];
            @endphp
            <ul class="cat-tabs" id="daily-tabs">
                <li><button class="cat-tab-btn active" onclick="filterDaily('all',this)">🛒 Semua</button></li>
                @foreach($dailyCategoryList as $cat)
                <li>
                    <button class="cat-tab-btn" onclick="filterDaily('{{ $cat }}',this)">
                        {{ $catConfig[$cat]['icon'] ?? '📦' }} {{ $catConfig[$cat]['label'] ?? ucfirst($cat) }}
                    </button>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="products-grid" id="daily-grid">
            @foreach($dailyProducts as $product)
            @php
                $icon = $catConfig[$product->category]['icon'] ?? '📦';
            @endphp
            <div class="product-card daily-card" data-cat="{{ $product->category }}"
                 onclick="openModal({{ $product->id }})">
                <div class="product-image-container">
                    <span class="product-category">{{ $icon }} {{ $catConfig[$product->category]['label'] ?? ucfirst($product->category) }}</span>
                    @if($product->stock <= 0)
                        <span style="position:absolute;top:16px;right:16px;background:#ef4444;color:white;font-size:10px;font-weight:700;padding:3px 10px;border-radius:99px;">HABIS</span>
                    @elseif($product->stock <= 20)
                        <span style="position:absolute;top:16px;right:16px;background:#f59e0b;color:white;font-size:10px;font-weight:700;padding:3px 10px;border-radius:99px;">HAMPIR HABIS</span>
                    @endif
                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="product-image" loading="lazy"
                         onerror="this.src='https://placehold.co/400x400/dcfce7/059669?text={{ urlencode($icon . ' ' . ($catConfig[$product->category]['label'] ?? $product->category)) }}'">
                </div>
                <div class="product-info">
                    <h3 class="product-title">{{ $product->name }}</h3>
                    <p class="product-desc">{{ Str::limit($product->description, 75) }}</p>
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;font-size:12px;font-weight:600;">
                        @if($product->stock > 20)
                            <span style="width:7px;height:7px;border-radius:50%;background:#10b981;display:inline-block;"></span>
                            <span style="color:#10b981;">Tersedia · Stok {{ $product->stock }}</span>
                        @elseif($product->stock > 0)
                            <span style="width:7px;height:7px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                            <span style="color:#f59e0b;">Hampir habis · {{ $product->stock }} sisa</span>
                        @else
                            <span style="width:7px;height:7px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
                            <span style="color:#ef4444;">Stok habis</span>
                        @endif
                    </div>
                    <div class="product-footer">
                        <span class="product-price">Rp {{ number_format($product->price,0,',','.') }}</span>
                        <span style="font-size:12px;color:#059669;font-weight:600;">Lihat Detail →</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div id="daily-empty" class="empty-state" style="display:none;">
            <div style="font-size:48px;margin-bottom:16px;">🛒</div>
            <p>Tidak ada produk untuk kategori ini.</p>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════════════
     PRODUCT DETAIL MODAL
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal-backdrop" id="product-modal" onclick="closeModalOutside(event)">
    <div class="modal-box" id="modal-box">
        <div class="modal-img-wrap">
            <span class="modal-cat-badge" id="modal-cat">—</span>
            <img id="modal-img" src="" alt="" class="modal-img"
                 onerror="this.src='https://placehold.co/500x500/f1f5f9/94a3b8?text=No+Image'">
        </div>
        <div class="modal-body" style="position:relative;">
            <button class="modal-close" onclick="closeModal()" title="Tutup">✕</button>
            <div id="modal-section-badge" style="display:inline-block;font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:4px 12px;border-radius:99px;color:white;margin-bottom:12px;"></div>
            <h2 id="modal-name" style="font-size:22px;line-height:1.3;color:var(--secondary);font-family:var(--font-heading);"></h2>
            <div class="modal-price" id="modal-price"></div>
            <div class="modal-stock-row" id="modal-stock-row">
                <span class="stock-dot" id="modal-stock-dot"></span>
                <span id="modal-stock-text"></span>
            </div>
            <p class="modal-desc" id="modal-desc"></p>
            <div style="margin-top:24px;">
                <form id="modal-cart-form" action="{{ route('cart.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" id="modal-product-id">
                    <input type="hidden" name="quantity" value="1">
                    <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px;">
                        <label style="font-size:13px;font-weight:600;color:var(--text-muted);">Jumlah:</label>
                        <div style="display:flex;align-items:center;gap:0;border:1.5px solid var(--border);border-radius:var(--radius-md);overflow:hidden;">
                            <button type="button" onclick="changeQty(-1)" style="width:36px;height:36px;background:var(--surface-muted);border:none;cursor:pointer;font-size:18px;font-weight:700;color:var(--text);">−</button>
                            <input type="number" name="quantity" id="modal-qty" value="1" min="1" max="99"
                                style="width:48px;height:36px;border:none;text-align:center;font-size:14px;font-weight:600;background:white;">
                            <button type="button" onclick="changeQty(1)"  style="width:36px;height:36px;background:var(--surface-muted);border:none;cursor:pointer;font-size:18px;font-weight:700;color:var(--text);">+</button>
                        </div>
                    </div>
                    <button type="submit" class="modal-add-btn" id="modal-add-btn">🛒 Tambah ke Keranjang</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
{{-- Data semua produk sebagai JSON untuk modal --}}
<script>
const allProducts = @json($fashionProducts->merge($dailyProducts)->values());
const catConfig = {
    sembako:    { icon:'🌾', label:'Sembako',    color:'#059669' },
    makanan:    { icon:'🍜', label:'Makanan',    color:'#d97706' },
    minuman:    { icon:'🥤', label:'Minuman',    color:'#0891b2' },
    kebersihan: { icon:'🧹', label:'Kebersihan', color:'#7c3aed' },
    perawatan:  { icon:'🧴', label:'Perawatan',  color:'#db2777' },
    kaos:       { icon:'👕', label:'Kaos',       color:'#4f46e5' },
    jaket:      { icon:'🧥', label:'Jaket',      color:'#4f46e5' },
    kemeja:     { icon:'👔', label:'Kemeja',     color:'#4f46e5' },
    celana:     { icon:'👖', label:'Celana',     color:'#4f46e5' },
    hoodie:     { icon:'🧢', label:'Hoodie',     color:'#4f46e5' },
};

// ─── Filter Fashion ────────────────────────────────────────────────────────────
function filterFashion(cat, btn) {
    document.querySelectorAll('#fashion-tabs .cat-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cards = document.querySelectorAll('.fashion-card');
    let visible = 0;
    cards.forEach(c => {
        const show = cat === 'all' || c.dataset.cat === cat;
        c.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('fashion-empty').style.display = visible === 0 ? 'block' : 'none';
    document.getElementById('fashion-grid').style.display  = visible === 0 ? 'none' : 'grid';
}

// ─── Filter Kebutuhan Harian ───────────────────────────────────────────────────
function filterDaily(cat, btn) {
    document.querySelectorAll('#daily-tabs .cat-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cards = document.querySelectorAll('.daily-card');
    let visible = 0;
    cards.forEach(c => {
        const show = cat === 'all' || c.dataset.cat === cat;
        c.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('daily-empty').style.display = visible === 0 ? 'block' : 'none';
    document.getElementById('daily-grid').style.display  = visible === 0 ? 'none' : 'grid';
}

// ─── Modal Product Detail ──────────────────────────────────────────────────────
function openModal(id) {
    const p = allProducts.find(x => x.id === id);
    if (!p) return;

    const cfg = catConfig[p.category] || { icon:'📦', label: p.category, color:'#64748b' };

    document.getElementById('modal-img').src       = p.image || '';
    document.getElementById('modal-img').alt       = p.name;
    document.getElementById('modal-cat').textContent = cfg.icon + ' ' + cfg.label;
    document.getElementById('modal-name').textContent = p.name;
    document.getElementById('modal-price').textContent = 'Rp ' + Number(p.price).toLocaleString('id-ID');
    document.getElementById('modal-desc').textContent = p.description || 'Produk berkualitas pilihan Z-MART.';
    document.getElementById('modal-product-id').value = p.id;
    document.getElementById('modal-qty').value = 1;

    // Section badge
    const sb = document.getElementById('modal-section-badge');
    sb.style.background = cfg.color;
    sb.textContent = cfg.icon + ' ' + cfg.label.toUpperCase();

    // Stock
    const dot  = document.getElementById('modal-stock-dot');
    const text = document.getElementById('modal-stock-text');
    const btn  = document.getElementById('modal-add-btn');
    if (p.stock > 20) {
        dot.style.background = '#10b981'; text.style.color = '#10b981';
        text.textContent = 'Tersedia · Stok ' + p.stock + ' unit';
        btn.disabled = false; btn.style.background = cfg.color; btn.style.color = 'white';
        btn.textContent = '🛒 Tambah ke Keranjang';
    } else if (p.stock > 0) {
        dot.style.background = '#f59e0b'; text.style.color = '#f59e0b';
        text.textContent = 'Hampir habis · ' + p.stock + ' tersisa';
        btn.disabled = false; btn.style.background = cfg.color; btn.style.color = 'white';
        btn.textContent = '🛒 Tambah ke Keranjang (' + p.stock + ' tersisa)';
    } else {
        dot.style.background = '#ef4444'; text.style.color = '#ef4444';
        text.textContent = 'Stok habis';
        btn.disabled = true; btn.style.background = '#e2e8f0'; btn.style.color = '#94a3b8';
        btn.textContent = 'Stok Habis';
    }

    document.getElementById('product-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('product-modal').classList.remove('open');
    document.body.style.overflow = '';
}

function closeModalOutside(e) {
    if (e.target === document.getElementById('product-modal')) closeModal();
}

// Qty controls
function changeQty(delta) {
    const inp = document.getElementById('modal-qty');
    const max = parseInt(inp.max) || 99;
    inp.value = Math.max(1, Math.min(max, parseInt(inp.value || 1) + delta));
}

// Close on Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
@endsection
