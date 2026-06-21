@extends('layouts.app')

@section('title', 'Daftar Akun')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card animate-fade-in" style="max-width: 480px;">

        <div class="auth-header">
            <h2>Buat Akun Baru</h2>
            <p>Daftar dan mulai belanja di Z-MART Boutique</p>
        </div>

        {{-- Alert dari Laravel (misal setelah redirect) --}}
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Alert dari JS --}}
        <div id="js-alert" style="display:none; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; font-weight: 500;"></div>

        <form id="register-form" novalidate>
            <div class="form-group">
                <label class="form-label" for="full_name">Nama Lengkap</label>
                <input type="text" class="form-control" id="full_name" placeholder="Masukkan nama lengkap" required autocomplete="name">
            </div>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" class="form-control" id="username"
                    placeholder="Contoh: johndoe123" required
                    pattern="^[a-zA-Z0-9_]{3,30}$"
                    title="3–30 karakter: huruf, angka, underscore"
                    autocomplete="username">
                <small style="color:var(--text-muted); font-size:11px;">3–30 karakter, hanya huruf/angka/underscore</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg_email">Alamat Email</label>
                <input type="email" class="form-control" id="reg_email" placeholder="contoh@email.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="reg_password">Password</label>
                <div style="position:relative;">
                    <input type="password" class="form-control" id="reg_password"
                        placeholder="Minimal 6 karakter" required minlength="6"
                        style="padding-right:44px;" autocomplete="new-password">
                    <button type="button" onclick="togglePass('reg_password', this)"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text-muted);">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg_confirm">Konfirmasi Password</label>
                <div style="position:relative;">
                    <input type="password" class="form-control" id="reg_confirm"
                        placeholder="Ulangi password" required minlength="6"
                        style="padding-right:44px;" autocomplete="new-password">
                    <button type="button" onclick="togglePass('reg_confirm', this)"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text-muted);">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="btn-register"
                style="width:100%;padding:13px;margin-top:8px;font-size:15px;font-weight:600;">
                Daftar Sekarang
            </button>
        </form>

        <div class="divider">atau daftar dengan</div>

        <button type="button" class="btn-google" id="btn-google">
            <img src="https://img.icons8.com/color/48/google-logo.png" alt="Google">
            Daftar / Masuk dengan Google
        </button>

        <div class="auth-footer-text" style="margin-top:20px;">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk Sekarang</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>
<script>
// ─── Firebase Init ────────────────────────────────────────────────────────────
const firebaseConfig = {
    apiKey:            "AIzaSyC15WbRiqlXS4bhYYXYd5tqWCikT7Hawuw",
    authDomain:        "zmartid-619c3.firebaseapp.com",
    projectId:         "zmartid-619c3",
    storageBucket:     "zmartid-619c3.firebasestorage.app",
    messagingSenderId: "1005876028696",
    appId:             "1:1005876028696:web:81dd1571b5660188cfea6d",
    measurementId:     "G-EE5NB7Y7H5"
};
firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();
auth.languageCode = 'id';

// ─── Helpers ──────────────────────────────────────────────────────────────────
const btnRegister = document.getElementById('btn-register');
const btnGoogle   = document.getElementById('btn-google');
const alertBox    = document.getElementById('js-alert');

function showAlert(msg, type = 'error') {
    alertBox.textContent = msg;
    alertBox.style.display = 'block';
    if (type === 'success') {
        alertBox.style.background = 'rgba(0,200,100,0.15)';
        alertBox.style.color      = '#00c864';
        alertBox.style.border     = '1px solid #00c864';
    } else {
        alertBox.style.background = 'rgba(255,71,87,0.12)';
        alertBox.style.color      = '#ff4757';
        alertBox.style.border     = '1px solid #ff4757';
    }
    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function setLoading(btn, text, loading = true) {
    btn.disabled    = loading;
    btn.textContent = loading ? text : btn.getAttribute('data-original');
    if (!btn.getAttribute('data-original') && loading) {
        btn.setAttribute('data-original', btn.textContent);
    }
}

function togglePass(id, btn) {
    const el = document.getElementById(id);
    if (el.type === 'password') { el.type = 'text'; btn.textContent = '🙈'; }
    else                        { el.type = 'password'; btn.textContent = '👁️'; }
}

// ─── POST ke Laravel (tanpa auto-login) ───────────────────────────────────────
async function saveToDatabase(payload) {
    const res = await fetch("{{ route('register.sync') }}", {
        method:  'POST',
        headers: {
            'Content-Type':  'application/json',
            'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
    });
    return res.json();
}

// ─── REGISTER via Email/Password ──────────────────────────────────────────────
document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    alertBox.style.display = 'none';

    const fullName  = document.getElementById('full_name').value.trim();
    const username  = document.getElementById('username').value.trim().toLowerCase();
    const email     = document.getElementById('reg_email').value.trim();
    const password  = document.getElementById('reg_password').value;
    const confirm   = document.getElementById('reg_confirm').value;

    // Validasi sisi client
    if (!fullName || !username || !email || !password) {
        showAlert('Semua kolom wajib diisi.'); return;
    }
    if (!/^[a-zA-Z0-9_]{3,30}$/.test(username)) {
        showAlert('Username hanya boleh huruf, angka, underscore (3–30 karakter).'); return;
    }
    if (password !== confirm) {
        showAlert('Konfirmasi password tidak cocok.'); return;
    }
    if (password.length < 6) {
        showAlert('Password minimal 6 karakter.'); return;
    }

    btnRegister.disabled = true;
    btnRegister.textContent = '⏳ Membuat akun Firebase...';

    try {
        // 1. Buat akun di Firebase Authentication
        const cred = await auth.createUserWithEmailAndPassword(email, password);
        const uid  = cred.user.uid;

        btnRegister.textContent = '💾 Menyimpan ke database...';

        // 2. Simpan ke database Laravel (TANPA login)
        const data = await saveToDatabase({ username, email, full_name: fullName, uid, password });

        if (data.success) {
            // 3. Logout dari Firebase (agar user harus login manual)
            await auth.signOut();

            showAlert('✅ ' + data.message, 'success');
            btnRegister.textContent = '✅ Berhasil! Mengalihkan ke Login...';

            // 4. Redirect ke halaman Login setelah 2 detik
            setTimeout(() => {
                window.location.href = "{{ route('login') }}?registered=1";
            }, 2000);

        } else {
            // Gagal simpan ke DB — hapus akun Firebase yang baru dibuat
            await cred.user.delete();
            showAlert('❌ ' + data.message);
            btnRegister.disabled = false;
            btnRegister.textContent = 'Daftar Sekarang';
        }

    } catch (err) {
        console.error('Register error:', err.code, err.message);
        let msg = 'Terjadi kesalahan. Coba lagi.';
        if (err.code === 'auth/email-already-in-use')  msg = 'Email sudah terdaftar di Firebase. Silakan langsung login.';
        else if (err.code === 'auth/weak-password')     msg = 'Password terlalu lemah. Gunakan minimal 6 karakter.';
        else if (err.code === 'auth/invalid-email')     msg = 'Format email tidak valid.';
        else if (err.message)                           msg = err.message;
        showAlert('❌ ' + msg);
        btnRegister.disabled = false;
        btnRegister.textContent = 'Daftar Sekarang';
    }
});

// ─── GOOGLE Sign-In / Register ───────────────────────────────────────────────
btnGoogle.addEventListener('click', async function() {
    btnGoogle.disabled    = true;
    btnGoogle.textContent = '⏳ Menghubungkan ke Google...';
    alertBox.style.display = 'none';

    const provider = new firebase.auth.GoogleAuthProvider();
    provider.setCustomParameters({ prompt: 'select_account' });

    try {
        const result = await auth.signInWithPopup(provider);
        await syncGoogleToServer(result.user);
    } catch (err) {
        console.error('Google error:', err.code, err.message);

        if (err.code === 'auth/unauthorized-domain') {
            const domain = window.location.hostname;
            showAlert(
                `❌ <strong>Domain belum diizinkan di Firebase Console.</strong><br><br>` +
                `Tambahkan domain berikut:<br>` +
                `<code style="background:rgba(255,255,255,0.1);padding:2px 8px;border-radius:4px;font-family:monospace;">${domain}</code><br><br>` +
                `<strong>Langkah:</strong><br>` +
                `1. Buka <a href="https://console.firebase.google.com/project/zmartid-619c3/authentication/settings" target="_blank" style="color:#ff4757;text-decoration:underline;">Firebase Console → Authentication → Settings</a><br>` +
                `2. Bagian <strong>Authorized domains</strong> → <strong>Add domain</strong><br>` +
                `3. Masukkan: <code style="background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:4px;">${domain}</code> → Save<br>` +
                `4. Muat ulang halaman dan coba lagi`
            );
        } else if (err.code === 'auth/popup-blocked') {
            btnGoogle.textContent = '⏳ Menggunakan redirect...';
            await auth.signInWithRedirect(provider).catch(re => {
                showAlert('❌ Gagal: ' + re.message);
            });
        } else if (err.code !== 'auth/popup-closed-by-user' && err.code !== 'auth/cancelled-popup-request') {
            showAlert('❌ Error [' + err.code + ']: ' + err.message);
        }

        if (err.code !== 'auth/popup-blocked') {
            btnGoogle.disabled  = false;
            btnGoogle.innerHTML = '<img src="https://img.icons8.com/color/48/google-logo.png" alt="Google"> Daftar / Masuk dengan Google';
        }
    }
});

// Tangkap redirect result (fallback)
auth.getRedirectResult().then(result => {
    if (result && result.user) {
        btnGoogle.disabled    = true;
        btnGoogle.textContent = '⏳ Menyinkronkan akun Google...';
        syncGoogleToServer(result.user);
    }
}).catch(err => {
    if (err.code && err.code !== 'auth/no-auth-event') {
        showAlert('❌ Error Google [' + err.code + ']: ' + err.message);
    }
});


async function syncGoogleToServer(user) {
    try {
        const res = await fetch("{{ route('google.sync') }}", {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email:     user.email,
                uid:       user.uid,
                full_name: user.displayName || 'Google User',
                avatar:    user.photoURL || '',
            }),
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            showAlert('❌ ' + (data.message || 'Gagal sinkronisasi Google.'));
            btnGoogle.disabled = false;
            btnGoogle.innerHTML = '<img src="https://img.icons8.com/color/48/google-logo.png" alt="Google"> Daftar / Masuk dengan Google';
        }
    } catch (err) {
        showAlert('❌ Gagal koneksi server: ' + err.message);
        btnGoogle.disabled = false;
        btnGoogle.innerHTML = '<img src="https://img.icons8.com/color/48/google-logo.png" alt="Google"> Daftar / Masuk dengan Google';
    }
}
</script>
@endsection
