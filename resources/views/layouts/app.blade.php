<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Z-MART Boutique') | Premium Clothing Store</title>
    
    <!-- Link to Custom Premium Vanilla CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <!-- Optional Page Styles -->
    @yield('styles')

    <!-- Premium Preloader CSS -->
    <style>
        #site-preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 999999;
            transition: opacity 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94), 
                        visibility 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        #site-preloader.fade-out {
            opacity: 0;
            visibility: hidden;
        }
        .loader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        .spinner-ring {
            width: 56px;
            height: 56px;
            border: 3.5px solid rgba(79, 70, 229, 0.08);
            border-radius: 50%;
            border-top: 3.5px solid #4f46e5;
            border-right: 3.5px solid #4f46e5;
            animation: preloader-spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        .loader-text {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 3px;
            color: #0f172a;
            animation: preloader-pulse 1.6s ease-in-out infinite;
        }
        @keyframes preloader-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes preloader-pulse {
            0%, 100% { opacity: 0.5; transform: scale(0.96); }
            50% { opacity: 1; transform: scale(1.04); }
        }
    </style>
</head>
<body>

    <!-- Modern Premium Preloader -->
    <div id="site-preloader" class="preloader">
        <div class="loader-content">
            <div class="spinner-ring"></div>
            <div class="loader-text">Z-MART</div>
        </div>
    </div>

    <!-- Header Navigation -->
    <header class="header">
        <div class="container navbar">
            <a href="{{ route('landing') }}" class="brand">
                Z-MART<span class="brand-dot"></span>
            </a>
            
            <ul class="nav-menu">
                <li><a href="{{ route('landing') }}" class="nav-link {{ request()->routeIs('landing') ? 'active' : '' }}">Home</a></li>
                <li><a href="{{ route('cart.index') }}" class="nav-link {{ request()->routeIs('cart.index') ? 'active' : '' }}">Katalog</a></li>
            </ul>
            
            <div class="nav-actions">
                <!-- Shopping Cart Icon with Badge -->
                <div class="cart-btn-wrapper">
                    <a href="{{ route('cart.index') }}" class="btn btn-secondary">
                        🛒 Keranjang
                    </a>
                    @php
                        $cartCount = 0;
                        if (auth()->check()) {
                            $cartCount = \App\Models\Cart::where('user_id', auth()->id())->sum('quantity');
                        } else {
                            $cartCount = array_sum(session()->get('cart', []));
                        }
                    @endphp
                    @if($cartCount > 0)
                        <span class="badge">{{ $cartCount }}</span>
                    @endif
                </div>

                <!-- Auth Navigation links -->
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Dashboard Admin</a>
                    @else
                        <a href="{{ route('user.dashboard') }}" class="btn btn-primary">Dashboard Saya</a>
                    @endif
                    
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="container" style="min-height: calc(100vh - 280px); margin-top: 30px;">
        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success" id="alert-success">
                <span>✅ {{ session('success') }}</span>
                <button class="alert-close" onclick="document.getElementById('alert-success').style.display='none'">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" id="alert-error">
                <span>❌ {{ session('error') }}</span>
                <button class="alert-close" onclick="document.getElementById('alert-error').style.display='none'">&times;</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning" id="alert-warning">
                <span>⚠️ {{ session('warning') }}</span>
                <button class="alert-close" onclick="document.getElementById('alert-warning').style.display='none'">&times;</button>
            </div>
        @endif

        <!-- Render Child View Content -->
        @yield('content')
    </main>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="container footer-grid">
            <div class="footer-about">
                <h3>Z-MART Boutique</h3>
                <p style="margin-top: 10px;">Boutique pakaian premium yang menghadirkan tren fashion terkini dengan bahan berkualitas tinggi dan harga yang terjangkau.</p>
            </div>
            <div>
                <h4 class="footer-title">Kategori</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('landing', ['category' => 'kaos']) }}">Kaos</a></li>
                    <li><a href="{{ route('landing', ['category' => 'jaket']) }}">Jaket</a></li>
                    <li><a href="{{ route('landing', ['category' => 'kemeja']) }}">Kemeja</a></li>
                    <li><a href="{{ route('landing', ['category' => 'celana']) }}">Celana</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-title">Hubungi Kami</h4>
                <p style="font-size: 14px; color: rgba(255,255,255,0.7);">Jl. Fashion Boulevard No. 99, Jakarta Selatan</p>
                <p style="font-size: 14px; color: rgba(255,255,255,0.7); margin-top: 8px;">support@zmart.id | +62 812-3456-7890</p>
            </div>
        </div>
        <div class="container footer-bottom">
            <p>&copy; 2026 Z-MART. All rights reserved.</p>
            <p>Made with 🤍 for Elegant Styles</p>
        </div>
    </footer>

    <!-- Optional Page Scripts -->
    @yield('scripts')

    <!-- Preloader Fade-out Script -->
    <script>
        window.addEventListener('load', function() {
            const preloader = document.getElementById('site-preloader');
            if (preloader) {
                preloader.classList.add('fade-out');
            }
        });
    </script>

</body>
</html>
