@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card animate-fade-in">

        <div class="auth-header">
            <h2>Masuk ke Akun</h2>
            <p>Selamat datang kembali di Z-MART Boutique</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:16px;">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom:16px;">❌ {{ session('error') }}</div>
        @endif

        <div id="register-notice" style="display:none; background:rgba(0,200,100,0.12); color:#00c864; border:1px solid #00c864; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; font-weight:500;">
            ✅ Akun berhasil dibuat! Silakan login dengan akun Anda.
        </div>

        <div id="js-alert" style="display:none; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; line-height:1.6;"></div>

        <form id="login-form" novalidate>
            <div class="form-group">
                <label class="form-label" for="login_identity">Email atau Username</label>
                <input type="text" class="form-control" id="login_identity"
                    placeholder="Contoh: user1 atau email@domain.com"
                    required autocomplete="username">
                <small style="color:var(--text-muted);font-size:11px;margin-top:4px;display:block;">
                    Akun default: <strong>admin</strong> (pass: admin123) &nbsp;|&nbsp; <strong>user1</strong> (pass: user123)
                </small>
            </div>

            <div class="form-group">
                <label class="form-label" for="login_password">Password</label>
                <div style="position:relative;">
                    <input type="password" class="form-control" id="login_password"
                        placeholder="••••••••" required
                        style="padding-right:44px;" autocomplete="current-password">
                    <button type="button" onclick="togglePass('login_password', this)"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text-muted);">👁️</button>
                </div>
            </div>

            <button type="submit" id="btn-login" class="btn btn-primary"
                style="width:100%;padding:13px;font-size:15px;font-weight:600;margin-top:4px;">
                Masuk Sekarang
            </button>
        </form>

        <div class="divider">atau masuk dengan</div>

        <button type="button" class="btn-google" id="btn-google">
            <img src="https://img.icons8.com/color/48/google-logo.png" alt="Google">
            Masuk dengan Google
        </button>

        <div class="auth-footer-text" style="margin-top:20px;">
            Belum punya akun? <a href="{{ route('register') }}">Daftar Sekarang</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>
<script>
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

// Notifikasi setelah register
if (new URLSearchParams(window.location.search).get('registered') === '1') {
    document.getElementById('register-notice').style.display = 'block';
    history.replaceState({}, '', window.location.pathname);
}

const btnLogin  = document.getElementById('btn-login');
const btnGoogle = document.getElementById('btn-google');
const alertBox  = document.getElementById('js-alert');

function showAlert(html, type = 'error') {
    alertBox.innerHTML      = html;
    alertBox.style.display  = 'block';
    alertBox.style.background = type === 'success'
        ? 'rgba(0,200,100,0.12)' : 'rgba(255,71,87,0.12)';
    alertBox.style.color  = type === 'success' ? '#00c864' : '#ff4757';
    alertBox.style.border = type === 'success' ? '1px solid #00c864' : '1px solid #ff4757';
    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function resetGoogleBtn() {
    btnGoogle.disabled  = false;
    btnGoogle.innerHTML = '<img src="https://img.icons8.com/color/48/google-logo.png" alt="Google"> Masuk dengan Google';
}

function togglePass(id, btn) {
    const el = document.getElementById(id);
    if (el.type === 'password') { el.type = 'text'; btn.textContent = '🙈'; }
    else                        { el.type = 'password'; btn.textContent = '👁️'; }
}

// ─── Sync session ke Laravel ─────────────────────────────────────────────────
async function syncLoginSession(email, uid) {
    btnLogin.textContent = '⏳ Menyinkronkan sesi...';
    const res  = await fetch("{{ route('login.sync') }}", {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, uid }),
    });
    const data = await res.json();
    if (data.success) {
        btnLogin.textContent = '✅ Berhasil!';
        window.location.href = data.redirect;
    } else {
        showAlert('❌ ' + (data.message || 'Gagal sinkronisasi sesi.'));
        btnLogin.disabled    = false;
        btnLogin.textContent = 'Masuk Sekarang';
    }
}

async function syncGoogleSession(user) {
    const res  = await fetch("{{ route('google.sync') }}", {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            email:     user.email,
            uid:       user.uid,
            full_name: user.displayName || 'Google User',
            avatar:    user.photoURL    || '',
        }),
    });
    const data = await res.json();
    if (data.success) {
        window.location.href = data.redirect;
    } else {
        showAlert('❌ ' + (data.message || 'Gagal sinkronisasi Google.'));
        resetGoogleBtn();
    }
}

// ─── EMAIL / PASSWORD LOGIN ───────────────────────────────────────────────────
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    alertBox.style.display = 'none';
    document.getElementById('register-notice').style.display = 'none';

    const identity = document.getElementById('login_identity').value.trim();
    const password = document.getElementById('login_password').value;

    if (!identity || !password) { showAlert('❌ Email/username dan password wajib diisi.'); return; }

    const email = identity.includes('@') ? identity : identity + '@zmart.id';

    btnLogin.disabled    = true;
    btnLogin.textContent = '⏳ Memverifikasi...';

    try {
        const cred = await auth.signInWithEmailAndPassword(email, password);
        await syncLoginSession(cred.user.email, cred.user.uid);
    } catch (err) {
        console.error('Login error:', err.code);
        let msg = 'Login gagal. Coba lagi.';

        if (err.code === 'auth/user-not-found' || err.code === 'auth/invalid-credential') {
            const isDefault = email === 'admin@zmart.id' || email === 'user1@zmart.id';
            if (isDefault) {
                try {
                    btnLogin.textContent = '⚙️ Inisialisasi akun bawaan...';
                    const cred2 = await auth.createUserWithEmailAndPassword(email, password);
                    await syncLoginSession(cred2.user.email, cred2.user.uid);
                    return;
                } catch (ce) {
                    msg = ce.code === 'auth/email-already-in-use'
                        ? 'Password salah untuk akun bawaan.'
                        : ce.message;
                }
            } else {
                msg = 'Akun tidak ditemukan. Pastikan email/username sudah terdaftar.';
            }
        } else if (err.code === 'auth/wrong-password')     msg = 'Password salah. Coba lagi.';
        else if (err.code === 'auth/invalid-email')        msg = 'Format email tidak valid.';
        else if (err.code === 'auth/too-many-requests')    msg = 'Terlalu banyak percobaan login. Coba lagi nanti.';
        else if (err.message)                              msg = err.message;

        showAlert('❌ ' + msg);
        btnLogin.disabled    = false;
        btnLogin.textContent = 'Masuk Sekarang';
    }
});

// ─── GOOGLE SIGN-IN ───────────────────────────────────────────────────────────
btnGoogle.addEventListener('click', async function() {
    btnGoogle.disabled    = true;
    btnGoogle.textContent = '⏳ Menghubungkan ke Google...';
    alertBox.style.display = 'none';

    const provider = new firebase.auth.GoogleAuthProvider();
    provider.setCustomParameters({ prompt: 'select_account' });

    try {
        // Coba popup terlebih dahulu
        const result = await auth.signInWithPopup(provider);
        await syncGoogleSession(result.user);
    } catch (err) {
        console.error('Google error:', err.code, err.message);

        if (err.code === 'auth/unauthorized-domain') {
            const domain = window.location.hostname;
            const port   = window.location.port ? ':' + window.location.port : '';
            showAlert(
                `❌ <strong>Domain belum diizinkan di Firebase Console.</strong><br><br>` +
                `Tambahkan domain berikut ke Firebase Console:<br>` +
                `<code style="background:rgba(255,255,255,0.1);padding:2px 8px;border-radius:4px;font-family:monospace;font-size:13px;">${domain}</code><br><br>` +
                `<strong>Langkah-langkah:</strong><br>` +
                `1. Buka <a href="https://console.firebase.google.com/project/zmartid-619c3/authentication/settings" target="_blank" style="color:#ff4757;text-decoration:underline;">Firebase Console → Authentication → Settings</a><br>` +
                `2. Scroll ke bagian <strong>Authorized domains</strong><br>` +
                `3. Klik <strong>Add domain</strong><br>` +
                `4. Masukkan: <code style="background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:4px;">${domain}</code> lalu Save<br>` +
                `5. Muat ulang halaman ini dan coba lagi`
            );
            resetGoogleBtn();

        } else if (err.code === 'auth/popup-blocked') {
            // Fallback ke redirect jika popup diblokir
            btnGoogle.textContent = '⏳ Menggunakan redirect...';
            try {
                await auth.signInWithRedirect(provider);
            } catch (re) {
                showAlert('❌ Gagal: ' + re.message);
                resetGoogleBtn();
            }

        } else if (err.code === 'auth/popup-closed-by-user' || err.code === 'auth/cancelled-popup-request') {
            resetGoogleBtn();
            // Silent — user sengaja tutup popup

        } else {
            showAlert('❌ Error [' + err.code + ']: ' + err.message);
            resetGoogleBtn();
        }
    }
});

// Tangkap hasil redirect (jika sebelumnya pakai fallback redirect)
auth.getRedirectResult().then(result => {
    if (result && result.user) {
        btnGoogle.disabled    = true;
        btnGoogle.textContent = '⏳ Menyinkronkan akun Google...';
        syncGoogleSession(result.user).catch(err => {
            showAlert('❌ Error: ' + err.message);
            resetGoogleBtn();
        });
    }
}).catch(err => {
    if (err.code && err.code !== 'auth/no-auth-event') {
        showAlert('❌ Error Google redirect [' + err.code + ']: ' + err.message);
    }
});
</script>
@endsection
