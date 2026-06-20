@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card animate-fade-in">
        <div class="auth-header">
            <h2>Masuk ke Akun</h2>
            <p>Silakan masuk untuk mengelola belanjaan Anda</p>
        </div>

        <!-- Alert Error (JS-driven) -->
        <div class="alert alert-danger" id="firebase-error-alert" style="display: none;">
            <span id="error-message">Terjadi kesalahan</span>
        </div>

        <form id="login-form">
            <!-- Email / Username Input -->
            <div class="form-group">
                <label class="form-label" for="login_identity">Email / Username</label>
                <input type="text" class="form-control" id="login_identity" placeholder="Contoh: user1 atau admin@zmart.id" required>
                <small style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">
                    Bisa menggunakan username default: <strong>admin</strong> atau <strong>user1</strong>
                </small>
            </div>

            <!-- Password Input -->
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" class="form-control" id="password" placeholder="••••••••" required style="padding-right: 44px;">
                    <button type="button" onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; color: var(--text-muted); padding: 4px;">
                        👁️
                    </button>
                </div>
                <small style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">
                    Password default: <strong>admin123</strong> (untuk admin) atau <strong>user123</strong> (untuk user)
                </small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px;" id="btn-submit">
                Masuk Sekarang
            </button>
        </form>

        <div class="divider">atau</div>

        <button type="button" class="btn-google" id="btn-google">
            <img src="https://img.icons8.com/color/48/google-logo.png" alt="Google Logo">
            Masuk dengan Google
        </button>

        <div class="auth-footer-text">
            Belum punya akun? <a href="{{ route('register') }}">Daftar Sekarang</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Firebase Compat JS SDK -->
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>

<script>
    // Initialize Firebase using the configuration generated dynamically from env
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

    const loginForm = document.getElementById('login-form');
    const errorAlert = document.getElementById('firebase-error-alert');
    const errorMessage = document.getElementById('error-message');
    const btnSubmit = document.getElementById('btn-submit');

    function showError(msg) {
        errorMessage.textContent = msg;
        errorAlert.style.display = 'flex';
        btnSubmit.disabled = false;
        btnSubmit.textContent = 'Masuk Sekarang';
    }

    function syncLoginSession(email, uid) {
        btnSubmit.textContent = 'Menyinkronkan sesi...';
        
        fetch("{{ route('login.sync') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: email, uid: uid })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                showError(data.message || 'Gagal sinkronisasi sesi.');
            }
        })
        .catch(error => {
            console.error('Error Sync:', error);
            showError('Terjadi kesalahan koneksi server lokal.');
        });
    }

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        errorAlert.style.display = 'none';
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Menghubungkan Firebase...';

        let identity = document.getElementById('login_identity').value.trim();
        let password = document.getElementById('password').value;

        // Auto append domain if it is just a username for firebase
        let email = identity;
        if (!identity.includes('@')) {
            email = identity + '@zmart.id';
        }

        // Map short 12345 password to 6+ character equivalents for Firebase Auth compliance
        if (password === '12345') {
            password = (email === 'admin@zmart.id') ? 'admin123' : 'user123';
        }

        // Authenticate with Firebase
        auth.signInWithEmailAndPassword(email, password)
            .then((userCredential) => {
                // Success: Sync session to Laravel
                syncLoginSession(userCredential.user.email, userCredential.user.uid);
            })
            .catch((error) => {
                console.warn('Firebase login warning/error:', error);
                
                // Self-healing: If default credentials and user not found, create silently
                if (
                    (error.code === 'auth/user-not-found' || error.code === 'auth/invalid-credential') && 
                    ((email === 'admin@zmart.id' && password === 'admin123') || 
                     (email === 'user1@zmart.id' && password === 'user123'))
                ) {
                    btnSubmit.textContent = 'Inisialisasi akun bawaan...';
                    auth.createUserWithEmailAndPassword(email, password)
                        .then((userCredential) => {
                            syncLoginSession(userCredential.user.email, userCredential.user.uid);
                        })
                        .catch((createError) => {
                            if (createError.code === 'auth/email-already-in-use') {
                                showError('Password yang Anda masukkan salah!');
                            } else {
                                showError('Gagal seeding akun default: ' + createError.message);
                            }
                        });
                } else if (error.code === 'auth/wrong-password' || error.code === 'auth/invalid-credential' || error.code === 'auth/user-not-found') {
                    showError('Email/Username atau Password salah!');
                } else if (error.code === 'auth/invalid-email') {
                    showError('Format email tidak valid.');
                } else {
                    showError(error.message);
                }
            });
    });

    // Google Sign-In trigger
    const btnGoogle = document.getElementById('btn-google');
    btnGoogle.addEventListener('click', function() {
        btnGoogle.disabled = true;
        btnGoogle.innerHTML = 'Menghubungkan Google...';
        errorAlert.style.display = 'none';

        const provider = new firebase.auth.GoogleAuthProvider();
        
        auth.signInWithPopup(provider)
            .then((result) => {
                const user = result.user;
                
                // Sync Google Session with Laravel Backend
                fetch("{{ route('google.sync') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        email: user.email,
                        uid: user.uid,
                        full_name: user.displayName || 'Google User',
                        avatar: user.photoURL || ''
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        showError(data.message || 'Gagal sinkronisasi akun Google.');
                        btnGoogle.innerHTML = '<img src="https://img.icons8.com/color/48/google-logo.png" alt="Google Logo"> Masuk dengan Google';
                        btnGoogle.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error Sync:', error);
                    showError('Koneksi server gagal saat sinkronisasi Google.');
                    btnGoogle.innerHTML = '<img src="https://img.icons8.com/color/48/google-logo.png" alt="Google Logo"> Masuk dengan Google';
                    btnGoogle.disabled = false;
                });
            })
            .catch((error) => {
                console.error('Google Sign-In Error:', error);
                if (error.code === 'auth/popup-closed-by-user') {
                    showError('Login Google dibatalkan.');
                } else {
                    showError(error.message);
                }
                btnGoogle.innerHTML = '<img src="https://img.icons8.com/color/48/google-logo.png" alt="Google Logo"> Masuk dengan Google';
                btnGoogle.disabled = false;
            });
    });

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
