<?php

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\User;

it('requires sign in before anyone can open spa workspaces', function () {
    $this->get('/')
        ->assertRedirect(route('login'));

    $this->get('/client')
        ->assertRedirect(route('login'));

    $this->get('/therapist')
        ->assertRedirect(route('login'));

    $this->get('/dashboard')
        ->assertRedirect(route('login'));
});

it('forbids non-clients from opening the client booking page', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get(route('client.appointments.create'))
        ->assertForbidden();
})->with([UserRole::Therapist, UserRole::Admin]);

it('allows a client to open the booking page', function () {
    $client = User::factory()->create(['role' => UserRole::Client]);

    $this->actingAs($client)
        ->get(route('client.appointments.create'))
        ->assertOk()
        ->assertSee('Booking as')
        ->assertDontSee(route('therapist.appointments.index'));
});

it('forbids non-clients from creating appointments', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->post(route('client.appointments.store'), [
            'client_name' => 'Blocked Client',
            'client_phone' => '09171234567',
            'service' => 'swedish',
            'appointment_at' => now()->addDays(2)->startOfHour()->format('Y-m-d\TH:i'),
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('appointments', 0);
})->with([UserRole::Therapist, UserRole::Admin]);

it('creates a pending appointment from an authenticated client account', function () {
    $therapist = User::factory()->therapist()->create();
    $client = User::factory()->create(['role' => UserRole::Client]);
    $appointmentAt = now()->addDays(2)->startOfHour();

    $response = $this->actingAs($client)->post(route('client.appointments.store'), [
        'client_name' => 'Someone Else',
        'client_phone' => '09171234567',
        'client_email' => 'someone@example.test',
        'service' => 'swedish',
        'therapist_id' => $therapist->id,
        'appointment_at' => $appointmentAt->format('Y-m-d\TH:i'),
        'notes' => 'Please use a lighter pressure.',
    ]);

    $response->assertRedirect(route('client.appointments.create'));
    $response->assertSessionHas('booking_confirmation');

    $appointment = Appointment::query()->firstOrFail();

    expect($appointment->client_id)->toBe($client->id)
        ->and($appointment->client_name)->toBe($client->name)
        ->and($appointment->client_phone)->toBe('09171234567')
        ->and($appointment->client_email)->toBe($client->email)
        ->and($appointment->service)->toBe('swedish')
        ->and($appointment->therapist_id)->toBe($therapist->id)
        ->and($appointment->appointment_at->format('Y-m-d H:i'))->toBe($appointmentAt->format('Y-m-d H:i'))
        ->and($appointment->status)->toBe(AppointmentStatus::Pending)
        ->and($appointment->notes)->toBe('Please use a lighter pressure.');
});

it('shows only the signed-in clients appointments on their dashboard', function () {
    $client = User::factory()->create(['role' => UserRole::Client]);
    $otherClient = User::factory()->create(['role' => UserRole::Client]);

    Appointment::query()->create([
        'client_id' => $client->id,
        'client_name' => $client->name,
        'client_phone' => '09171234567',
        'service' => 'deep_tissue',
        'appointment_at' => now()->addDays(2)->setTime(10, 0),
        'status' => AppointmentStatus::Confirmed,
    ]);

    Appointment::query()->create([
        'client_id' => $otherClient->id,
        'client_name' => $otherClient->name,
        'client_phone' => '09179876543',
        'service' => 'hot_stone',
        'appointment_at' => now()->addDays(5)->setTime(10, 0),
        'status' => AppointmentStatus::Pending,
    ]);

    $this->actingAs($client)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Your appointments')
        ->assertSee('Deep tissue massage')
        ->assertDontSee('Hot stone massage')
        ->assertSee('Upcoming')
        ->assertDontSee(route('therapist.appointments.index'));
});

it('rejects invalid appointment details without creating a booking', function () {
    $client = User::factory()->create(['role' => UserRole::Client]);

    $response = $this->actingAs($client)->from('/client')->post(route('client.appointments.store'), [
        'client_name' => '',
        'client_phone' => '',
        'service' => 'unknown',
        'appointment_at' => now()->subDay()->format('Y-m-d\TH:i'),
    ]);

    $response->assertRedirect('/client')
        ->assertSessionHasErrors(['client_phone', 'service', 'appointment_at']);

    $this->assertDatabaseCount('appointments', 0);
});

it('only accepts a therapist account for an assigned appointment', function () {
    $client = User::factory()->create(['role' => UserRole::Client]);
    $bookingClient = User::factory()->create(['role' => UserRole::Client]);

    $response = $this->actingAs($bookingClient)->from('/client')->post(route('client.appointments.store'), [
        'client_name' => 'Jamie Client',
        'client_phone' => '09171234567',
        'service' => 'swedish',
        'therapist_id' => $client->id,
        'appointment_at' => now()->addDays(2)->startOfHour()->format('Y-m-d\TH:i'),
    ]);

    $response->assertRedirect('/client')
        ->assertSessionHasErrors('therapist_id');

    $this->assertDatabaseCount('appointments', 0);
});

it('forbids non-therapists from opening the therapist workspace', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get(route('therapist.appointments.index'))
        ->assertForbidden();
})->with([UserRole::Client, UserRole::Admin]);

it('allows a therapist to open the appointment workspace', function () {
    $therapist = User::factory()->therapist()->create();

    $this->actingAs($therapist)
        ->get(route('therapist.appointments.index'))
        ->assertOk()
        ->assertSee('Appointment schedule');
});

it('forbids non-therapists from changing appointment status', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);
    $appointment = Appointment::query()->create([
        'client_name' => 'Jamie Client',
        'client_phone' => '09171234567',
        'service' => 'swedish',
        'appointment_at' => now()->addDays(2),
        'status' => AppointmentStatus::Pending,
    ]);

    $this->actingAs($user)
        ->patch(route('therapist.appointments.update-status', $appointment), [
            'status' => AppointmentStatus::Confirmed->value,
        ])
        ->assertForbidden();

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Pending);
})->with([UserRole::Client, UserRole::Admin]);

it('lets an authenticated therapist update an appointment status', function () {
    $therapist = User::factory()->therapist()->create();
    $appointment = Appointment::query()->create([
        'client_name' => 'Jamie Client',
        'client_phone' => '09171234567',
        'service' => 'swedish',
        'appointment_at' => now()->addDays(2),
        'status' => AppointmentStatus::Pending,
    ]);

    $response = $this->actingAs($therapist)->patch(
        route('therapist.appointments.update-status', $appointment),
        ['status' => AppointmentStatus::Confirmed->value],
    );

    $response->assertRedirect();
    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Confirmed);
});

it('rejects unsupported appointment status changes', function () {
    $therapist = User::factory()->therapist()->create();
    $appointment = Appointment::query()->create([
        'client_name' => 'Jamie Client',
        'client_phone' => '09171234567',
        'service' => 'swedish',
        'appointment_at' => now()->addDays(2),
        'status' => AppointmentStatus::Pending,
    ]);

    $response = $this->actingAs($therapist)->from('/therapist')->patch(
        route('therapist.appointments.update-status', $appointment),
        ['status' => AppointmentStatus::Pending->value],
    );

    $response->assertRedirect('/therapist')
        ->assertSessionHasErrors('status');
    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Pending);
});

it('escapes client names and does not show client contact information on the therapist board', function () {
    $therapist = User::factory()->therapist()->create();
    Appointment::query()->create([
        'client_name' => '<script>alert(1)</script>',
        'client_phone' => '09171234567',
        'client_email' => 'private@example.test',
        'service' => 'swedish',
        'appointment_at' => now()->addDays(2),
        'status' => AppointmentStatus::Pending,
    ]);

    $this->actingAs($therapist)->get('/therapist')
        ->assertOk()
        ->assertSee('<script>alert(1)</script>')
        ->assertDontSee('09171234567')
        ->assertDontSee('private@example.test');
});
