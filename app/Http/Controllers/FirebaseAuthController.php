<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class FirebaseAuthController extends Controller
{
    /**
     * Sync user login session from Firebase Auth.
     */
    public function loginSync(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'uid' => 'required|string',
        ]);

        $email = $request->email;
        $uid = $request->uid;

        // Find user by email
        $user = User::where('email', $email)->first();

        // If user not found, try finding by username (part before @)
        if (!$user) {
            $username = explode('@', $email)[0];
            $user = User::where('username', $username)->first();
        }

        if (!$user) {
            // If the user doesn't exist locally, we create them as a customer
            $username = explode('@', $email)[0];
            $user = User::create([
                'username' => $username,
                'email' => $email,
                'password' => 'firebase_auth', // Placeholder
                'full_name' => ucwords(str_replace(['.', '_'], ' ', $username)),
                'role' => 'customer',
                'google_uid' => $uid,
            ]);
        } else {
            // Update Google UID if not set
            if (empty($user->google_uid)) {
                $user->google_uid = $uid;
                $user->save();
            }
        }

        // Log the user into Laravel session
        Auth::login($user);

        // Sync guest session cart to database
        CartController::syncSessionCartToDb($user->id);

        // Determine redirect path
        $redirect = $user->role === 'admin' ? route('admin.dashboard') : route('user.dashboard');

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'redirect' => $redirect,
        ]);
    }

    /**
     * Sync user registration from Firebase Auth.
     */
    public function registerSync(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'email' => 'required|email',
            'full_name' => 'required|string|max:100',
            'uid' => 'required|string',
        ]);

        // Check if user already exists
        $user = User::where('email', $request->email)
                    ->orWhere('username', $request->username)
                    ->first();

        if ($user) {
            // If user exists, just update UID and log in
            $user->google_uid = $request->uid;
            $user->save();
        } else {
            // Create new customer
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => 'firebase_auth',
                'full_name' => $request->full_name,
                'role' => 'customer',
                'google_uid' => $request->uid,
            ]);
        }

        // Log in
        Auth::login($user);

        // Sync guest session cart to database
        CartController::syncSessionCartToDb($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Registered and logged in successfully',
            'redirect' => route('user.dashboard'),
        ]);
    }

    /**
     * Sync user login/registration session from Firebase Google Sign-In.
     */
    public function googleSync(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'uid' => 'required|string',
            'full_name' => 'required|string',
            'avatar' => 'nullable|string',
        ]);

        $email = $request->email;
        $uid = $request->uid;
        $fullName = $request->full_name;
        $avatar = $request->avatar;

        // Search for user by email first
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Generate unique username
            $username = explode('@', $email)[0];
            $baseUsername = $username;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            // Create new customer user
            $user = User::create([
                'username' => $username,
                'email' => $email,
                'password' => 'google_auth',
                'full_name' => $fullName,
                'role' => 'customer',
                'google_uid' => $uid,
                'avatar' => $avatar,
            ]);
        } else {
            // Update Google UID and avatar if changed/missing
            $user->google_uid = $uid;
            if ($avatar && empty($user->avatar)) {
                $user->avatar = $avatar;
            }
            $user->save();
        }

        // Log the user in
        Auth::login($user);

        // Sync session cart
        CartController::syncSessionCartToDb($user->id);

        // Determine redirect path
        $redirect = $user->role === 'admin' ? route('admin.dashboard') : route('user.dashboard');

        return response()->json([
            'success' => true,
            'message' => 'Google session synced successfully',
            'redirect' => $redirect,
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')->with('success', 'Logged out successfully');
    }
}
