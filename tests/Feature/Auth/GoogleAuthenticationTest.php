<?php

use App\Models\User;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;

test('login page offers Google sign in', function () {
    Socialite::fake('google');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Continue with Google');
});

test('visitors can start Google authentication', function () {
    Socialite::fake('google');

    $this->get(route('google.redirect'))
        ->assertRedirect();
});

test('expired Google state returns visitors to login with a retry message', function () {
    Socialite::shouldReceive('driver')
        ->once()
        ->with('google')
        ->andThrow(new InvalidStateException);

    $this->get(route('google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'email' => 'Your Google sign-in session expired or could not be verified. Please try again.',
        ]);

    $this->assertGuest();
});

test('Google sign in creates a verified client account', function () {
    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-user-123',
        'name' => 'Google Client',
        'email' => 'google-client@example.com',
        'email_verified' => true,
    ]));

    $response = $this->get(route('google.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Google Client',
        'email' => 'google-client@example.com',
        'google_id' => 'google-user-123',
        'role' => 'client',
    ]);
    expect(User::query()->where('google_id', 'google-user-123')->value('email_verified_at'))
        ->not->toBeNull();
});

test('Google sign in links a verified existing account', function () {
    $user = User::factory()->unverified()->create([
        'email' => 'existing@example.com',
    ]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-existing-123',
        'email' => 'existing@example.com',
        'email_verified' => true,
    ]));

    $this->get(route('google.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'google_id' => 'google-existing-123',
    ]);
    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('Google sign in rejects an unverified email', function () {
    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-unverified-123',
        'email' => 'unverified@example.com',
        'email_verified' => false,
    ]));

    $this->get(route('google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', [
        'google_id' => 'google-unverified-123',
    ]);
});

test('Google sign in does not replace a different linked Google account', function () {
    User::factory()->create([
        'email' => 'linked@example.com',
        'google_id' => 'google-original-123',
    ]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-other-123',
        'email' => 'linked@example.com',
        'email_verified' => true,
    ]));

    $this->get(route('google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseHas('users', [
        'email' => 'linked@example.com',
        'google_id' => 'google-original-123',
    ]);
});
