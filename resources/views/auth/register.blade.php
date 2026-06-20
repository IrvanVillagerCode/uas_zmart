@extends('layouts.app')

@section('title', 'Daftar Akun')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card animate-fade-in" style="max-width: 500px;">
        <div class="auth-header">
            <h2>Daftar Akun Baru</h2>
            <p>Bergabunglah dengan Z-MART untuk mulai belanja pakaian premium</p>
        </div>

        <!-- Alert Error (JS-driven) -->
        <div class="alert alert-danger" id="firebase-error-alert" style="display: none;">
            <span id="error-message">Terjadi kesalahan</span>
        </div>

        <form id="register-form">
            <!-- Full Name -->
            <div class="form-group">
                <label class="form-label" for="full_name">Nama Lengkap</label>
                <input type="text" class="form-control" id="full_name" placeholder="Masukkan nama lengkap Anda" required>
            </div>

            <!-- Username -->
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" class="form-control" id="username" placeholder="Masukkan username unik" required pattern="^[a-zA-Z0-9_]{3,20}$" title="Username harus 3-20 karakter alfanumerik (huruf, angka, underscore)">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">Alamat Email</label>
                <input type="email" class="form-control" id="email" placeholder="contoh@domain.com" required>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">Password (Minimal 6 karakter)</label>
                <div style="position: relative;">
                    <input type="password" class="form-control" id="password" placeholder="••••••••" required minlength="6" style="padding-right: 44px;">
                    <button type="button" onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; color: var(--text-muted); padding: 4px;">
                        👁️
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px;" id="btn-submit">
                Daftar Sekarang
            </button>
        </form>

        <div class="divider">atau</div>

        <button type="button" class="btn-google" id="btn-google">
            <img src="https://img.icons8.com/color/48/google-logo.png" alt="Google Logo">
            Daftar dengan Google
        </button>

        <div class="auth-footer-text">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk Sekarang</a>
        </div>
    </div>
</div>
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

    const registerForm = document.getElementById('register-form');
    const errorAlert = document.getElementById('firebase-error-alert');
    const errorMessage = document.getElementById('error-message');
    const btnSubmit = document.getElementById('btn-submit');

    function showError(msg) {
        errorMessage.textContent = msg;
        errorAlert.style.display = 'flex';
        btnSubmit.disabled = false;
        btnSubmit.textContent = 'Daftar Sekarang';
    }

    function syncRegister(username, email, fullName, uid) {
        btnSubmit.textContent = 'Menyimpan ke database lokal...';
        
        fetch("{{ route('register.sync') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                username: username,
                email: email,
                full_name: fullName,
                uid: uid
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                showError(data.message || 'Gagal menyimpan akun di server lokal.');
            }
        })
        .catch(error => {
            console.error('Error Sync:', error);
            showError('Terjadi kesalahan koneksi server lokal.');
        });
    }

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        errorAlert.style.display = 'none';
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Mendaftarkan di Firebase...';

        const fullName = document.getElementById('full_name').value.trim();
        const username = document.getElementById('username').value.trim().toLowerCase();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        // Register in Firebase Auth first
        auth.createUserWithEmailAndPassword(email, password)
            .then((userCredential) => {
                // Success: Sync user record to Laravel database
                syncRegister(username, email, fullName, userCredential.user.uid);
            })
            .catch((error) => {
                console.error('Firebase Register Error:', error);
                if (error.code === 'auth/email-already-in-use') {
                    showError('Alamat email sudah terdaftar di Firebase!');
                } else if (error.code === 'auth/weak-password') {
                    showError('Password terlalu lemah! Gunakan minimal 6 karakter.');
                } else {
                    showError(error.message);
                }
            });
    });

    // Google Sign-In trigger (handles both login and registration)
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
                        btnGoogle.innerHTML = '<img src="https://img.icons8.com/color/48/google-logo.png" alt="Google Logo"> Daftar dengan Google';
                        btnGoogle.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error Sync:', error);
                    showError('Koneksi server gagal saat sinkronisasi Google.');
                    btnGoogle.innerHTML = '<img src="https://img.icons8.com/color/48/google-logo.png" alt="Google Logo"> Daftar dengan Google';
                    btnGoogle.disabled = false;
                });
            })
            .catch((error) => {
                console.error('Google Sign-In Error:', error);
                if (error.code === 'auth/popup-closed-by-user') {
                    showError('Pendaftaran Google dibatalkan.');
                } else {
                    showError(error.message);
                }
                btnGoogle.innerHTML = '<img src="https://img.icons8.com/color/48/google-logo.png" alt="Google Logo"> Daftar dengan Google';
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
