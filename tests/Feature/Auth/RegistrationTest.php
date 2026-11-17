<?php

test('visitors can open the registration page', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Register')
        ->assertSee('Continue with Google')
        ->assertSee(route('google.redirect'));
});

test('visitors can register as clients', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'client',
    ]);
});

test('login page provides a register button', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Register')
        ->assertSee(route('register'));
});
