<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->is_active) {
            return back()->withErrors(['email' => 'Your account has been disabled. Contact administrator.'])->withInput();
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->withInput($request->only('email'));
    }

    /**
     * Redirect the user to the provider (Google or Facebook) authentication page.
     */
    public function redirectToProvider(string $provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->withErrors(['email' => 'Unsupported login provider.']);
        }

        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        if (empty($clientId) || empty($clientSecret)) {
            $providerName = ucfirst($provider);
            return redirect()->route('login')->withErrors([
                'email' => "{$providerName} Login is not configured yet. Please set {$providerName} credentials in the .env file."
            ]);
        }

        try {
            $driver = Socialite::driver($provider);

            // In local development, configure Guzzle to avoid cURL 60 SSL certificate issues on Windows/XAMPP
            if (app()->isLocal()) {
                $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }

            if ($provider === 'facebook') {
                $driver->scopes(['email', 'public_profile']);
            }

            return $driver->redirect();
        } catch (\Throwable $e) {
            Log::error("{$provider} OAuth redirect error: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to connect with ' . ucfirst($provider) . ': ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtain the user information from provider (Google or Facebook).
     */
    public function handleProviderCallback(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->withErrors(['email' => 'Unsupported login provider.']);
        }

        if ($request->has('error') || $request->has('error_message')) {
            $error = $request->get('error_description', $request->get('error_message', 'Login was cancelled.'));
            return redirect()->route('login')->withErrors(['email' => $error]);
        }

        try {
            $driver = Socialite::driver($provider);

            // In local development, configure Guzzle to avoid cURL 60 SSL certificate issues on Windows/XAMPP
            if (app()->isLocal()) {
                $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }

            if ($provider === 'facebook') {
                $driver->fields(['name', 'first_name', 'last_name', 'email', 'picture.type(large)']);
            }

            // Use stateless to avoid session state mismatches across localhost / 127.0.0.1
            $socialUser = $driver->stateless()->user();
        } catch (\Throwable $e) {
            Log::error("{$provider} OAuth callback error: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Failed to authenticate with ' . ucfirst($provider) . ': ' . $e->getMessage()
            ]);
        }

        $email = $socialUser->getEmail();
        $providerId = $socialUser->getId();
        $providerIdColumn = "{$provider}_id";

        if (!$email && !$providerId) {
            return redirect()->route('login')->withErrors([
                'email' => "Unable to retrieve account details from " . ucfirst($provider) . "."
            ]);
        }

        // 1. Try finding user by provider ID
        $user = User::where($providerIdColumn, $providerId)->first();

        // 2. If not found by provider ID, try finding user by email
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            // Check active status
            if (!$user->is_active) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been disabled. Contact administrator.'
                ]);
            }

            // Link provider and update avatar if not set
            $updates = [];
            if (empty($user->{$providerIdColumn})) {
                $updates[$providerIdColumn] = $providerId;
            }
            if (empty($user->avatar) && $socialUser->getAvatar()) {
                $updates['avatar'] = $socialUser->getAvatar();
            }
            if (empty($user->email_verified_at)) {
                $updates['email_verified_at'] = now();
            }
            if (!empty($updates)) {
                $user->update($updates);
            }
        } else {
            // Register new ERP user from OAuth
            $user = User::create([
                'name'              => $socialUser->getName() ?: ($socialUser->getNickname() ?: 'ERP User'),
                'email'             => $email ?: "{$provider}_{$providerId}@shreegiriraj.erp",
                'password'          => Hash::make(Str::random(32)),
                'role'              => 'staff',
                'is_active'         => true,
                $providerIdColumn   => $providerId,
                'avatar'            => $socialUser->getAvatar(),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully!');
    }

    public function switchUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $targetUser = User::findOrFail($request->user_id);

        if (!$targetUser->is_active) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'This account is inactive.'], 403);
            }
            return back()->withErrors(['user' => 'This account is inactive.']);
        }

        Auth::login($targetUser);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Switched to ' . $targetUser->name . ' (' . ucfirst($targetUser->role) . ')',
                'user' => [
                    'id'   => $targetUser->id,
                    'name' => $targetUser->name,
                    'role' => $targetUser->role,
                ]
            ]);
        }

        return back()->with('success', 'Switched user to ' . $targetUser->name);
    }
}

