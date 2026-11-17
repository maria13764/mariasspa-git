<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admins can view the account management page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Manage accounts');
});

test('admins can view the add account page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.create'))
        ->assertSee('Add account')
        ->assertSee('Therapist')
        ->assertSee('Client');
});

test('admins can create an account with the selected role', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'New Therapist',
            'email' => 'new-therapist@mariasspa.test',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'role' => UserRole::Therapist->value,
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('status', 'user-created');

    $user = User::query()->where('email', 'new-therapist@mariasspa.test')->firstOrFail();

    expect($user->role)->toBe(UserRole::Therapist)
        ->and(Hash::check('StrongPassword123!', $user->password))->toBeTrue();
});

test('non-admin roles cannot create accounts', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => 'Blocked User',
            'email' => 'blocked@mariasspa.test',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'role' => UserRole::Client->value,
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'blocked@mariasspa.test']);
})->with([UserRole::Therapist, UserRole::Client]);

test('admins cannot create accounts with an invalid role or duplicate email', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'existing@mariasspa.test']);
    $validDetails = [
        'name' => 'New Account',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ];

    $this->actingAs($admin)
        ->from(route('admin.users.create'))
        ->post(route('admin.users.store'), $validDetails + [
            'email' => 'invalid-role@mariasspa.test',
            'role' => 'owner',
        ])
        ->assertSessionHasErrors('role');

    $this->from(route('admin.users.create'))
        ->post(route('admin.users.store'), $validDetails + [
            'email' => 'existing@mariasspa.test',
            'role' => UserRole::Client->value,
        ])
        ->assertSessionHasErrors('email');

    $this->assertDatabaseMissing('users', ['email' => 'invalid-role@mariasspa.test']);
});

test('non-admin roles cannot manage accounts', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
})->with([UserRole::Therapist, UserRole::Client]);

test('admins can edit a users name and role but not email or password', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@mariasspa.test',
    ]);
    $originalPassword = $user->password;

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'Updated Name',
            'role' => UserRole::Therapist->value,
            'email' => 'changed@example.com',
            'password' => 'changed-password',
        ])
        ->assertRedirect(route('admin.users.index'));

    $user->refresh();

    expect($user->name)->toBe('Updated Name')
        ->and($user->role)->toBe(UserRole::Therapist)
        ->and($user->email)->toBe('original@mariasspa.test')
        ->and($user->password)->toBe($originalPassword)
        ->and(Hash::check('password', $user->password))->toBeTrue();
});

test('admins can delete another account', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admins cannot delete their own account from account management', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});
test('guests are redirected to sign in from the home page', function () {
    $this->get('/')->assertRedirect(route('login'));
});
