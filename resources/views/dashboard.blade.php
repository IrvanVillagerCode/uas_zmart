@extends('layouts.app')

@section('title', 'Dashboard Saya')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-grid">
        
        <!-- Sidebar Panel -->
        <aside class="sidebar-card animate-fade-in">
            <div class="profile-card">
                @php $user = auth()->user(); @endphp
                @if($user->avatar && !str_starts_with($user->avatar, 'http') && !str_starts_with($user->avatar, '/') && mb_strlen($user->avatar) <= 4)
                    <div class="emoji-avatar-placeholder" style="display: flex; align-items: center; justify-content: center; font-size: 40px; background-color: var(--primary-light); width: 80px; height: 80px; border-radius: 50%; border: 3px solid var(--primary-light); margin: 0 auto 12px auto; user-select: none;">
                        {{ $user->avatar }}
                    </div>
                @else
                    <img src="{{ $user->avatar ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80' }}" alt="{{ $user->full_name }}" class="profile-avatar">
                @endif
                <h3 class="profile-name">{{ $user->full_name }}</h3>
                <span class="profile-role">Pelanggan</span>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="javascript:void(0)" class="sidebar-link active" id="tab-orders-btn" onclick="switchTab('orders')">
                        📦 Pesanan Saya
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="sidebar-link" id="tab-profile-btn" onclick="switchTab('profile')">
                        ⚙️ Pengaturan Profil
                    </a>
                </li>
                <li>
                    <a href="{{ route('cart.index') }}" class="sidebar-link">
                        🛒 Keranjang Belanja
                    </a>
                </li>
                <li>
                    <a href="{{ route('landing') }}" class="sidebar-link">
                        🛍️ Kembali Belanja
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content Panel -->
        <div class="dashboard-content animate-fade-in" style="animation-delay: 0.1s;">
            
            <!-- Orders Tab Pane -->
            <div id="tab-orders" class="tab-pane">
                <div style="margin-bottom: 30px;">
                    <h2 style="font-size: 26px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Selamat Datang Kembali, {{ auth()->user()->username }}!</h2>
                    <p style="color: var(--text-muted); font-size: 14px;">Di sini Anda dapat melacak status pesanan pakaian Anda dan memantau riwayat belanja.</p>
                </div>

                <!-- Orders Table Section -->
                <h3 style="font-size: 18px; margin-bottom: 16px; color: var(--secondary); border-bottom: 1.5px solid var(--border); padding-bottom: 10px;">Riwayat Pesanan Pakaian</h3>

                @if($orders->isEmpty())
                    <div style="text-align: center; padding: 40px; background: var(--background); border-radius: var(--radius-md); border: 1px solid var(--border); margin-top: 10px;">
                        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 16px;">Anda belum melakukan pemesanan pakaian.</p>
                        <a href="{{ route('landing') }}" class="btn btn-primary btn-sm">Belanja Sekarang</a>
                    </div>
                @else
                    <div style="overflow-x: auto;">
                        <table class="cart-table" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Metode</th>
                                    <th>Total Belanja</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td style="font-weight: 600; font-family: monospace; font-size: 14px; color: var(--secondary);">
                                            {{ $order->order_number }}
                                        </td>
                                        <td style="font-size: 13px; color: var(--text-muted);">
                                            {{ $order->created_at ? date('d M Y, H:i', strtotime($order->created_at)) : '-' }}
                                        </td>
                                        <td style="font-size: 13px; color: var(--text-muted);">
                                            {{ $order->payment_method ?? 'COD' }}
                                        </td>
                                        <td style="font-weight: 700; font-size: 14px; color: var(--secondary);">
                                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            @if($order->status === 'pending')
                                                <span class="status-badge status-pending">Pending</span>
                                            @elseif($order->status === 'success')
                                                <span class="status-badge status-success">Terkirim</span>
                                            @elseif($order->status === 'cancelled')
                                                <span class="status-badge status-cancelled">Dibatalkan</span>
                                            @else
                                                <span class="status-badge status-failed">Gagal</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 8px;">
                                                <!-- Toggle Item Details Link -->
                                                <button class="btn btn-secondary btn-sm" onclick="toggleDetails('{{ $order->id }}')">
                                                    🔍 Detail
                                                </button>
                                                <a href="{{ route('user.order.invoice', $order->id) }}" target="_blank" class="btn btn-primary btn-sm" style="background-color: var(--primary); color: white;">
                                                    🖨️ Cetak Nota
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Hidden details row -->
                                    <tr id="details-{{ $order->id }}" style="display: none; background-color: var(--surface-muted);">
                                        <td colspan="6" style="padding: 16px;">
                                            <div style="font-size: 13px;">
                                                <p style="margin-bottom: 8px;"><strong>Alamat Pengiriman:</strong> {{ $order->shipping_address }}</p>
                                                @if($order->notes)
                                                    <p style="margin-bottom: 12px;"><strong>Catatan Tambahan:</strong> <em>"{{ $order->notes }}"</em></p>
                                                @endif
                                                
                                                <h4 style="font-size: 13px; font-weight: 600; color: var(--secondary); margin-bottom: 8px; border-bottom: 1px solid var(--border); padding-bottom: 4px;">Daftar Item Pakaian:</h4>
                                                
                                                <div class="order-items-list">
                                                    @foreach($order->items as $item)
                                                        <div class="order-item-row">
                                                            <span>
                                                                @if($item->product)
                                                                    👕 <strong>{{ $item->product->name }}</strong>
                                                                @else
                                                                    👕 <strong>{{ $item->product_name }}</strong>
                                                                @endif
                                                                (x{{ $item->quantity }})
                                                            </span>
                                                            <span style="font-weight: 600; font-family: monospace;">
                                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Profile Tab Pane -->
            <div id="tab-profile" class="tab-pane" style="display: none;">
                <div style="margin-bottom: 30px;">
                    <h2 style="font-size: 26px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Pengaturan Akun</h2>
                    <p style="color: var(--text-muted); font-size: 14px;">Perbarui data informasi profil diri dan akun belanja Anda.</p>
                </div>

                <h3 style="font-size: 18px; margin-bottom: 24px; color: var(--secondary); border-bottom: 1.5px solid var(--border); padding-bottom: 10px;">Detail Profil</h3>

                <!-- Validation Errors Display -->
                @if ($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 20px;">
                        <ul style="margin: 0; padding-left: 20px; text-align: left;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" style="max-width: 600px; text-align: left;">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label" style="margin-bottom: 8px; display: block;">Foto Profil / Avatar</label>
                        
                        <!-- Toggle Selector for Type of Avatar -->
                        <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                            <button type="button" class="btn btn-secondary btn-sm active" id="avatar-type-preset" onclick="selectAvatarType('preset')">Koleksi Butik</button>
                            <button type="button" class="btn btn-secondary btn-sm" id="avatar-type-emoji" onclick="selectAvatarType('emoji')">Emoji Fashion</button>
                            <button type="button" class="btn btn-secondary btn-sm" id="avatar-type-upload" onclick="selectAvatarType('upload')">Unggah Foto</button>
                        </div>

                        <!-- 1. Preset Collection Grid -->
                        <div id="avatar-sec-preset" class="avatar-sec">
                            <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px;">
                                @php
                                    $presets = [
                                        'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
                                        'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=150&auto=format&fit=crop&q=80',
                                        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
                                        'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
                                        'https://images.unsplash.com/photo-1501196354995-cbb51c65aaea?w=150&auto=format&fit=crop&q=80',
                                        'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=150&auto=format&fit=crop&q=80',
                                    ];
                                @endphp
                                @foreach($presets as $preset)
                                    <div class="preset-img-wrapper {{ auth()->user()->avatar === $preset ? 'selected' : '' }}" onclick="choosePreset('{{ $preset }}', this)" style="cursor: pointer; border-radius: 50%; overflow: hidden; aspect-ratio: 1; border: 3px solid transparent; transition: var(--transition);">
                                        <img src="{{ $preset }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 2. Emoji Grid -->
                        <div id="avatar-sec-emoji" class="avatar-sec" style="display: none;">
                            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; text-align: center;">
                                @php
                                    $emojis = ['👕', '👗', '👔', '🧥', '👒', '🕶️', '👜', '👟', '🧣', '🧢'];
                                @endphp
                                @foreach($emojis as $em)
                                    <div class="emoji-item {{ auth()->user()->avatar === $em ? 'selected' : '' }}" onclick="chooseEmoji('{{ $em }}', this)" style="font-size: 28px; cursor: pointer; padding: 6px; border-radius: var(--radius-md); border: 2px solid transparent; transition: var(--transition); background: var(--surface-muted);">
                                        {{ $em }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 3. Custom File Upload -->
                        <div id="avatar-sec-upload" class="avatar-sec" style="display: none;">
                            <input type="file" name="avatar_file" class="form-control" accept="image/*">
                        </div>

                        <!-- Hidden Input to store current/selected avatar value -->
                        <input type="hidden" name="avatar" id="selected-avatar-val" value="{{ auth()->user()->avatar }}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="full_name">Nama Lengkap</label>
                        <input type="text" name="full_name" id="full_name" class="form-control" value="{{ old('full_name', auth()->user()->full_name) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control" value="{{ old('username', auth()->user()->username) }}" required pattern="^[a-zA-Z0-9_]{3,20}$" title="Username harus 3-20 karakter alfanumerik (huruf, angka, underscore)">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Alamat Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                        💾 Simpan Perubahan
                    </button>
                </form>

                @if(auth()->user()->password !== 'google_auth')
                    <!-- Ganti Password Form -->
                    <h3 style="font-size: 18px; margin-top: 40px; margin-bottom: 24px; color: var(--secondary); border-bottom: 1.5px solid var(--border); padding-bottom: 10px;">Ganti Password</h3>

                    <div class="alert alert-danger" id="password-error-alert" style="display: none;">
                        <span id="password-error-message">Terjadi kesalahan</span>
                    </div>
                    <div class="alert alert-success" id="password-success-alert" style="display: none;">
                        <span>✅ Password Anda berhasil diperbarui di Firebase dan database lokal!</span>
                    </div>

                    <form id="change-password-form" style="max-width: 600px; text-align: left;">
                        <div class="form-group">
                            <label class="form-label" for="current_password">Password Saat Ini</label>
                            <div style="position: relative;">
                                <input type="password" id="current_password" class="form-control" placeholder="••••••••" required style="padding-right: 44px;">
                                <button type="button" onclick="togglePasswordVisibility('current_password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; color: var(--text-muted); padding: 4px;">
                                    👁️
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="new_password">Password Baru (Minimal 6 karakter)</label>
                            <div style="position: relative;">
                                <input type="password" id="new_password" class="form-control" placeholder="••••••••" required minlength="6" style="padding-right: 44px;">
                                <button type="button" onclick="togglePasswordVisibility('new_password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; color: var(--text-muted); padding: 4px;">
                                    👁️
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Konfirmasi Password Baru</label>
                            <div style="position: relative;">
                                <input type="password" id="confirm_password" class="form-control" placeholder="••••••••" required minlength="6" style="padding-right: 44px;">
                                <button type="button" onclick="togglePasswordVisibility('confirm_password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; color: var(--text-muted); padding: 4px;">
                                    👁️
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="btn-submit-password" style="margin-top: 10px;">
                            🔑 Perbarui Password
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
    function toggleDetails(orderId) {
        const detailsRow = document.getElementById(`details-${orderId}`);
        if (detailsRow.style.display === 'none') {
            detailsRow.style.display = 'table-row';
        } else {
            detailsRow.style.display = 'none';
        }
    }

    function switchTab(tabName) {
        // Hide all panes
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.style.display = 'none';
        });
        
        // Remove active class from all sidebar links
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Show target pane
        document.getElementById(`tab-${tabName}`).style.display = 'block';
        
        // Add active class to target link
        document.getElementById(`tab-${tabName}-btn`).classList.add('active');
        
        // Save to localStorage so if page reloads, user stays on the same tab
        localStorage.setItem('dashboard_active_tab', tabName);
    }
    
    function selectAvatarType(type) {
        document.querySelectorAll('.avatar-sec').forEach(sec => {
            sec.style.display = 'none';
        });
        document.getElementById('avatar-type-preset').classList.remove('active');
        document.getElementById('avatar-type-emoji').classList.remove('active');
        document.getElementById('avatar-type-upload').classList.remove('active');

        document.getElementById(`avatar-sec-${type}`).style.display = 'block';
        document.getElementById(`avatar-type-${type}`).classList.add('active');
    }

    function choosePreset(url, el) {
        document.getElementById('selected-avatar-val').value = url;
        document.querySelectorAll('.preset-img-wrapper').forEach(item => {
            item.classList.remove('selected');
        });
        el.classList.add('selected');
    }

    function chooseEmoji(emoji, el) {
        document.getElementById('selected-avatar-val').value = emoji;
        document.querySelectorAll('.emoji-item').forEach(item => {
            item.classList.remove('selected');
        });
        el.classList.add('selected');
    }

    // On load, restore active tab and avatar selection view
    window.addEventListener('DOMContentLoaded', () => {
        const hasErrors = @json($errors->any());
        const profileUpdated = @json(session('success') && str_contains(session('success'), 'Profil'));
        
        let activeTab = 'orders';
        if (hasErrors || profileUpdated) {
            activeTab = 'profile';
        } else {
            const savedTab = localStorage.getItem('dashboard_active_tab');
            if (savedTab && document.getElementById(`tab-${savedTab}`)) {
                activeTab = savedTab;
            }
        }
        
        switchTab(activeTab);

        // Auto-detect current avatar type on load to show appropriate tab
        const currentAvatar = "{{ auth()->user()->avatar }}";
        if (currentAvatar && !currentAvatar.startsWith('http') && !currentAvatar.startsWith('/') && currentAvatar.length <= 4) {
            selectAvatarType('emoji');
        } else if (currentAvatar && (currentAvatar.startsWith('http') || currentAvatar.startsWith('/'))) {
            const presets = [
                'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=150&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1501196354995-cbb51c65aaea?w=150&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=150&auto=format&fit=crop&q=80',
            ];
            if (presets.includes(currentAvatar)) {
                selectAvatarType('preset');
            } else {
                selectAvatarType('upload');
            }
        }
    });
</script>
@endsection

@section('scripts')
<!-- Firebase Compat JS SDK -->
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>

<script>
    // Initialize Firebase
    const firebaseConfig = {
        apiKey: "{{ config('firebase.api_key') }}",
        authDomain: "{{ config('firebase.auth_domain') }}",
        projectId: "{{ config('firebase.project_id') }}",
        storageBucket: "{{ config('firebase.storage_bucket') }}",
        messagingSenderId: "{{ config('firebase.messaging_sender_id') }}",
        appId: "{{ config('firebase.app_id') }}",
        measurementId: "{{ config('firebase.measurement_id') }}"
    };
    
    firebase.initializeApp(firebaseConfig);
    const auth = firebase.auth();

    const changePasswordForm = document.getElementById('change-password-form');
    if (changePasswordForm) {
        const passwordErrorAlert = document.getElementById('password-error-alert');
        const passwordErrorMessage = document.getElementById('password-error-message');
        const passwordSuccessAlert = document.getElementById('password-success-alert');
        const btnSubmitPassword = document.getElementById('btn-submit-password');

        function showPasswordError(msg) {
            passwordErrorMessage.textContent = msg;
            passwordErrorAlert.style.display = 'flex';
            passwordSuccessAlert.style.display = 'none';
            btnSubmitPassword.disabled = false;
            btnSubmitPassword.textContent = '🔑 Perbarui Password';
        }

        changePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            passwordErrorAlert.style.display = 'none';
            passwordSuccessAlert.style.display = 'none';
            btnSubmitPassword.disabled = true;
            btnSubmitPassword.textContent = 'Menghubungkan Firebase...';

            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                showPasswordError('Password baru dan konfirmasi password tidak cocok!');
                return;
            }

            // In Firebase, we need to reauthenticate first to update password
            // Find current user email from PHP
            const email = "{{ auth()->user()->email }}";

            // Map short 12345 password if they entered it
            let checkPassword = currentPassword;
            if (checkPassword === '12345') {
                checkPassword = (email === 'admin@zmart.id') ? 'admin123' : 'user123';
            }

            // Reauthenticate
            const user = auth.currentUser;
            if (!user) {
                // If currentUser is not loaded yet, try listening to authStateChanged or use email auth sign in directly
                auth.signInWithEmailAndPassword(email, checkPassword)
                    .then((userCredential) => {
                        return userCredential.user.updatePassword(newPassword);
                    })
                    .then(() => {
                        syncPasswordToBackend(newPassword);
                    })
                    .catch((error) => {
                        console.error('Firebase Password Reset Error:', error);
                        if (error.code === 'auth/wrong-password' || error.code === 'auth/invalid-credential') {
                            showPasswordError('Password saat ini yang Anda masukkan salah!');
                        } else {
                            showPasswordError(error.message);
                        }
                    });
            } else {
                const credential = firebase.auth.EmailAuthProvider.credential(user.email, checkPassword);
                user.reauthenticateWithCredential(credential)
                    .then(() => {
                        return user.updatePassword(newPassword);
                    })
                    .then(() => {
                        syncPasswordToBackend(newPassword);
                    })
                    .catch((error) => {
                        console.error('Firebase Password Reset Error:', error);
                        if (error.code === 'auth/wrong-password' || error.code === 'auth/invalid-credential') {
                            showPasswordError('Password saat ini yang Anda masukkan salah!');
                        } else {
                            showPasswordError(error.message);
                        }
                    });
            }
        });

        function syncPasswordToBackend(newPassword) {
            btnSubmitPassword.textContent = 'Menyinkronkan sesi...';
            
            fetch("{{ route('user.password.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ password: newPassword })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    passwordSuccessAlert.style.display = 'flex';
                    changePasswordForm.reset();
                    btnSubmitPassword.disabled = false;
                    btnSubmitPassword.textContent = '🔑 Perbarui Password';
                } else {
                    showPasswordError(data.message || 'Gagal sinkronisasi password ke server lokal.');
                }
            })
            .catch(error => {
                console.error('Error Sync:', error);
                showPasswordError('Terjadi kesalahan koneksi server lokal.');
            });
        }
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁️';
        }
    }
</script>
@endsection
