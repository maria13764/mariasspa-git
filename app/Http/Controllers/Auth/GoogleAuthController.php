<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your Google sign-in session expired or could not be verified. Please try again.',
            ]);
        }

        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();

        if (
            ! is_string($googleId)
            || trim($googleId) === ''
            || ! is_string($email)
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
            || ($googleUser->user['email_verified'] ?? false) !== true
        ) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google did not provide a verified email address.',
            ]);
        }

        $googleId = trim($googleId);
        $email = Str::lower($email);

        $user = User::query()->where('google_id', $googleId)->first();

        if ($user === null) {
            $user = User::query()->where('email', $email)->first();

            if ($user?->google_id !== null && $user->google_id !== $googleId) {
                return redirect()->route('login')->withErrors([
                    'email' => 'This email is already linked to another Google account.',
                ]);
            }

            if ($user === null) {
                $name = $googleUser->getName();
                $name = is_string($name) && trim($name) !== ''
                    ? Str::substr(trim($name), 0, 255)
                    : Str::before($email, '@');

                $user = new User([
                    'name' => $name,
                    'email' => $email,
                    'password' => Str::random(64),
                    'google_id' => $googleId,
                    'role' => UserRole::Client,
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();

                event(new Registered($user));
            } else {
                $user->forceFill([
                    'google_id' => $googleId,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
