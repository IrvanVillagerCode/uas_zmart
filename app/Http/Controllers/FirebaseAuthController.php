<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FirebaseAuthController extends Controller
{
    /**
     * Sync user login session from Firebase Auth (email/password).
     * Called after successful Firebase signInWithEmailAndPassword.
     */
    public function loginSync(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'uid'   => 'required|string',
        ]);

        try {
            $email = $request->email;
            $uid   = $request->uid;

            // Find by email first
            $user = User::where('email', $email)->first();

            // Fallback: find by username (for @zmart.id accounts like admin, user1)
            if (!$user) {
                $username = explode('@', $email)[0];
                $user = User::where('username', $username)->first();
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun tidak ditemukan di database. Silakan daftar terlebih dahulu.',
                ], 404);
            }

            // Update google_uid if not yet set
            if (empty($user->google_uid)) {
                $user->google_uid = $uid;
                $user->save();
            }

            // Login ke Laravel session
            Auth::login($user, false);

            // Merge guest cart ke DB
            CartController::syncSessionCartToDb($user->id);

            $redirect = $user->role === 'admin'
                ? route('admin.dashboard')
                : route('user.dashboard');

            return response()->json([
                'success'  => true,
                'message'  => 'Login berhasil.',
                'redirect' => $redirect,
                'role'     => $user->role,
            ]);

        } catch (\Exception $e) {
            Log::error('LoginSync Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register new user from Firebase Auth.
     * Saves user to DB only — does NOT log in.
     * Frontend should redirect to login after this.
     */
    public function registerSync(Request $request)
    {
        $request->validate([
            'username'  => 'required|string|max:50',
            'email'     => 'required|email',
            'full_name' => 'required|string|max:100',
            'uid'       => 'required|string',
            'password'  => 'required|string|min:6',
        ]);

        try {
            // Cek apakah email atau username sudah ada
            if (User::where('email', $request->email)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email sudah terdaftar di database.',
                ], 422);
            }

            if (User::where('username', $request->username)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username sudah digunakan.',
                ], 422);
            }

            // Buat user baru
            User::create([
                'username'   => strtolower($request->username),
                'email'      => $request->email,
                'password'   => $request->password, // Disimpan as-is, auth via Firebase
                'full_name'  => $request->full_name,
                'role'       => 'customer',
                'google_uid' => $request->uid,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil dibuat! Silakan login dengan akun Anda.',
            ]);

        } catch (\Exception $e) {
            Log::error('RegisterSync Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan akun: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Google Sign-In sync — login or auto-register via Google OAuth.
     * Called after getRedirectResult() on the frontend.
     */
    public function googleSync(Request $request)
    {
        $request->validate([
            'email'     => 'required|email',
            'uid'       => 'required|string',
            'full_name' => 'required|string',
            'avatar'    => 'nullable|string',
        ]);

        try {
            $email    = $request->email;
            $uid      = $request->uid;
            $fullName = $request->full_name;
            $avatar   = $request->avatar;

            $user = User::where('email', $email)->first();

            if (!$user) {
                // Auto-register via Google
                $username = preg_replace('/[^a-zA-Z0-9_]/', '_', explode('@', $email)[0]);
                $base = $username;
                $i = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $base . $i++;
                }

                $user = User::create([
                    'username'   => $username,
                    'email'      => $email,
                    'password'   => 'google_oauth',
                    'full_name'  => $fullName,
                    'role'       => 'customer',
                    'google_uid' => $uid,
                    'avatar'     => $avatar,
                ]);
            } else {
                // Update UID dan avatar jika perlu
                $user->google_uid = $uid;
                if ($avatar && empty($user->avatar)) {
                    $user->avatar = $avatar;
                }
                $user->save();
            }

            Auth::login($user, false);
            CartController::syncSessionCartToDb($user->id);

            $redirect = $user->role === 'admin'
                ? route('admin.dashboard')
                : route('user.dashboard');

            return response()->json([
                'success'  => true,
                'message'  => 'Login Google berhasil.',
                'redirect' => $redirect,
                'role'     => $user->role,
            ]);

        } catch (\Exception $e) {
            Log::error('GoogleSync Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi Google: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout dari Laravel session.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }
}
