<?php

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\User;

it('creates, retrieves, updates, and deletes an appointment for an admin', function () {
    $admin = User::factory()->admin()->create();

    $createResponse = $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->postJson('/api/appointments', [
            'client_name' => 'Jamie Client',
            'client_phone' => '09171234567',
            'client_email' => 'jamie@example.test',
            'service' => 'swedish',
            'appointment_at' => now()->addDays(2)->toDateTimeString(),
            'notes' => 'Use a lighter pressure.',
        ]);

    $createResponse
        ->assertCreated()
        ->assertJsonPath('message', 'Appointment created successfully.')
        ->assertJsonPath('data.status', AppointmentStatus::Pending->value);

    $appointmentId = $createResponse->json('data.id');
    $this->assertDatabaseHas('appointments', [
        'id' => $appointmentId,
        'client_name' => 'Jamie Client',
        'status' => AppointmentStatus::Pending->value,
    ]);

    $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->getJson('/api/appointments')
        ->assertOk()
        ->assertJsonPath('data.0.id', $appointmentId);

    $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->getJson("/api/appointments/{$appointmentId}")
        ->assertOk()
        ->assertJsonPath('data.client_email', 'jamie@example.test');

    $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->patchJson("/api/appointments/{$appointmentId}", [
            'status' => AppointmentStatus::Confirmed->value,
            'notes' => 'Confirmed by admin.',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', AppointmentStatus::Confirmed->value)
        ->assertJsonPath('data.notes', 'Confirmed by admin.');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointmentId,
        'status' => AppointmentStatus::Confirmed->value,
        'notes' => 'Confirmed by admin.',
    ]);

    $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->deleteJson("/api/appointments/{$appointmentId}")
        ->assertOk()
        ->assertJsonPath(
            'message',
            'Appointment deleted successfully. This appointment has been permanently removed.',
        )
        ->assertJsonPath('data.id', $appointmentId)
        ->assertJsonPath('data.client_name', 'Jamie Client')
        ->assertJsonPath('data.client_phone', '09171234567');

    $this->assertDatabaseMissing('appointments', ['id' => $appointmentId]);
});

it('allows admins to create multiple appointments without linking a client account', function () {
    $admin = User::factory()->admin()->create();
    $appointmentDetails = [
        'client_name' => 'Walk-in Client',
        'client_phone' => '09171234567',
        'service' => 'swedish',
        'appointment_at' => now()->addDays(2)->toDateTimeString(),
    ];

    $firstResponse = $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->postJson('/api/appointments', $appointmentDetails);
    $secondResponse = $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->postJson('/api/appointments', $appointmentDetails);

    $firstResponse->assertCreated();
    $secondResponse->assertCreated();

    $this->assertDatabaseCount('appointments', 2);
    $this->assertDatabaseHas('appointments', [
        'id' => $firstResponse->json('data.id'),
        'client_id' => null,
        'client_name' => 'Walk-in Client',
    ]);
    $this->assertDatabaseHas('appointments', [
        'id' => $secondResponse->json('data.id'),
        'client_id' => null,
        'client_name' => 'Walk-in Client',
    ]);
});

it('duplicates an existing appointment through the collection POST with a new ID', function () {
    $admin = User::factory()->admin()->create();
    $client = User::factory()->create(['role' => UserRole::Client]);
    $therapist = User::factory()->therapist()->create();
    $appointment = Appointment::query()->create([
        'client_id' => $client->id,
        'client_name' => 'Jamie Client',
        'client_phone' => '09171234567',
        'client_email' => 'jamie@example.test',
        'service' => 'swedish',
        'therapist_id' => $therapist->id,
        'appointment_at' => now()->addDays(2),
        'status' => AppointmentStatus::Confirmed,
        'notes' => 'Keep the pressure light.',
    ]);

    $response = $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->postJson('/api/appointments', [
            'duplicate_id' => $appointment->id,
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Appointment duplicated successfully.')
        ->assertJsonPath('data.client_id', $client->id)
        ->assertJsonPath('data.client_name', 'Jamie Client')
        ->assertJsonPath('data.client_phone', '09171234567')
        ->assertJsonPath('data.client_email', 'jamie@example.test')
        ->assertJsonPath('data.service', 'swedish')
        ->assertJsonPath('data.therapist_id', $therapist->id)
        ->assertJsonPath('data.status', AppointmentStatus::Confirmed->value)
        ->assertJsonPath('data.notes', 'Keep the pressure light.');

    $duplicateId = $response->json('data.id');

    expect($duplicateId)->not->toBe($appointment->id);

    $this->assertDatabaseCount('appointments', 2);
    $this->assertDatabaseHas('appointments', [
        'id' => $duplicateId,
        'client_id' => $client->id,
        'client_name' => 'Jamie Client',
        'client_phone' => '09171234567',
        'client_email' => 'jamie@example.test',
        'service' => 'swedish',
        'therapist_id' => $therapist->id,
        'status' => AppointmentStatus::Confirmed->value,
        'notes' => 'Keep the pressure light.',
    ]);
});

it('creates a new appointment from a GET response body without reusing its ID', function () {
    $admin = User::factory()->admin()->create();
    $existingAppointment = Appointment::query()->create([
        'client_name' => 'Jamie Client',
        'client_phone' => '09171234567',
        'client_email' => 'jamie@example.test',
        'service' => 'swedish',
        'appointment_at' => now()->subDays(2),
        'status' => AppointmentStatus::Pending,
        'notes' => 'Keep the pressure light.',
    ]);

    $response = $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->postJson('/api/appointments', [
            'data' => [[
                'id' => $existingAppointment->id,
                'client_id' => null,
                'client_name' => 'Jamie Client',
                'client_phone' => '09171234567',
                'client_email' => 'jamie@example.test',
                'service' => 'swedish',
                'therapist_id' => null,
                'appointment_at' => $existingAppointment->appointment_at->toISOString(),
                'status' => AppointmentStatus::Pending->value,
                'notes' => 'Keep the pressure light.',
                'created_at' => $existingAppointment->created_at->toISOString(),
                'updated_at' => $existingAppointment->updated_at->toISOString(),
            ]],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 1,
            ],
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.client_name', 'Jamie Client')
        ->assertJsonPath('data.client_phone', '09171234567')
        ->assertJsonPath('data.service', 'swedish');

    $newAppointmentId = $response->json('data.id');

    expect($newAppointmentId)->not->toBe($existingAppointment->id);

    $this->assertDatabaseCount('appointments', 2);
    $this->assertDatabaseHas('appointments', [
        'id' => $newAppointmentId,
        'client_name' => 'Jamie Client',
        'client_phone' => '09171234567',
        'service' => 'swedish',
        'appointment_at' => $existingAppointment->appointment_at->toDateTimeString(),
        'notes' => 'Keep the pressure light.',
    ]);
});

it('creates an appointment from a raw JSON array containing a data object', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->postJson('/api/appointments', [[
            'data' => [
                'id' => 9999,
                'client_name' => 'Raw JSON Client',
                'client_phone' => '09171234567',
                'client_email' => 'raw@example.test',
                'service' => 'swedish',
                'appointment_at' => now()->addDays(2)->toISOString(),
                'status' => AppointmentStatus::Pending->value,
                'notes' => 'Created from the raw JSON body.',
            ],
        ]]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.client_name', 'Raw JSON Client')
        ->assertJsonPath('data.client_email', 'raw@example.test')
        ->assertJsonPath('data.service', 'swedish')
        ->assertJsonPath('data.notes', 'Created from the raw JSON body.');

    $this->assertDatabaseCount('appointments', 1);
    $this->assertDatabaseHas('appointments', [
        'client_name' => 'Raw JSON Client',
        'client_phone' => '09171234567',
        'client_email' => 'raw@example.test',
        'service' => 'swedish',
        'status' => AppointmentStatus::Pending->value,
        'notes' => 'Created from the raw JSON body.',
    ]);
    $this->assertDatabaseMissing('appointments', ['id' => 9999]);
});

it('replaces an appointment from a GET response body without changing its ID', function () {
    $admin = User::factory()->admin()->create();
    $target = Appointment::query()->create([
        'client_name' => 'Old Client',
        'client_phone' => '09170000000',
        'service' => 'swedish',
        'appointment_at' => now()->addDays(3),
        'status' => AppointmentStatus::Pending,
        'notes' => 'Old notes',
    ]);
    $updatedAppointmentAt = now()->subDays(5);

    $response = $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->putJson("/api/appointments/{$target->id}", [
            'data' => [[
                'id' => 999,
                'client_id' => null,
                'client_name' => 'Updated Client',
                'client_phone' => '09171234567',
                'client_email' => 'updated@example.test',
                'service' => 'deep_tissue',
                'therapist_id' => null,
                'appointment_at' => $updatedAppointmentAt->toISOString(),
                'status' => AppointmentStatus::Confirmed->value,
                'notes' => 'Updated notes',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ]],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 1,
            ],
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $target->id)
        ->assertJsonPath('data.client_name', 'Updated Client')
        ->assertJsonPath('data.client_phone', '09171234567')
        ->assertJsonPath('data.client_email', 'updated@example.test')
        ->assertJsonPath('data.service', 'deep_tissue')
        ->assertJsonPath('data.status', AppointmentStatus::Confirmed->value)
        ->assertJsonPath('data.notes', 'Updated notes');

    $this->assertDatabaseCount('appointments', 1);
    $this->assertDatabaseHas('appointments', [
        'id' => $target->id,
        'client_name' => 'Updated Client',
        'client_phone' => '09171234567',
        'client_email' => 'updated@example.test',
        'service' => 'deep_tissue',
        'appointment_at' => $updatedAppointmentAt->toDateTimeString(),
        'status' => AppointmentStatus::Confirmed->value,
        'notes' => 'Updated notes',
    ]);
    $this->assertDatabaseMissing('appointments', ['id' => 999]);
});

it('returns 422 when an admin tries to duplicate a missing appointment', function () {
    $admin = User::factory()->admin()->create();

    $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->postJson('/api/appointments', [
            'duplicate_id' => 999,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('duplicate_id');
});

it('returns 403 when a non-admin tries to duplicate an appointment', function () {
    $client = User::factory()->create(['role' => UserRole::Client]);
    $appointment = Appointment::query()->create([
        'client_name' => 'Jamie Client',
        'client_phone' => '09171234567',
        'service' => 'swedish',
        'appointment_at' => now()->addDays(2),
    ]);

    $this->withToken($client->createToken('test-token')->plainTextToken)
        ->postJson('/api/appointments', [
            'duplicate_id' => $appointment->id,
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('appointments', 1);
});

it('returns 401 when the appointment API request has no bearer token', function () {
    $this->getJson('/api/appointments')->assertUnauthorized();
});

it('does not accept basic authentication for the appointment API', function () {
    $admin = User::factory()->admin()->create();

    $this->withBasicAuth($admin->email, 'password')
        ->getJson('/api/appointments')
        ->assertUnauthorized();
});

it('issues a bearer token to an admin that can access appointments', function () {
    $admin = User::factory()->admin()->create();

    $tokenResponse = $this->postJson('/api/tokens', [
        'email' => $admin->email,
        'password' => 'password',
        'device_name' => 'Postman',
    ]);

    $tokenResponse
        ->assertCreated()
        ->assertJsonPath('token_type', 'Bearer');

    $this->withToken($tokenResponse->json('token'))
        ->getJson('/api/appointments')
        ->assertOk();
});

it('forbids non-admins from obtaining an API bearer token', function () {
    $client = User::factory()->create();

    $this->postJson('/api/tokens', [
        'email' => $client->email,
        'password' => 'password',
        'device_name' => 'Postman',
    ])->assertForbidden();
});

it('does not issue an API bearer token when the password is incorrect', function () {
    $admin = User::factory()->admin()->create();

    $this->postJson('/api/tokens', [
        'email' => $admin->email,
        'password' => 'incorrect-password',
        'device_name' => 'Postman',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('returns 403 when a non-admin requests appointment data', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->withToken($user->createToken('test-token')->plainTextToken)
        ->getJson('/api/appointments')
        ->assertForbidden();
})->with([
    'client' => UserRole::Client,
    'therapist' => UserRole::Therapist,
]);

it('returns 422 and does not create an appointment when input is invalid', function () {
    $admin = User::factory()->admin()->create();

    $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->postJson('/api/appointments', [
            'client_name' => '',
            'client_phone' => '',
            'service' => 'unknown',
            'appointment_at' => 'not-a-date',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['client_name', 'client_phone', 'service', 'appointment_at']);

    $this->assertDatabaseCount('appointments', 0);
});

it('returns 404 when an admin requests an appointment that does not exist', function () {
    $admin = User::factory()->admin()->create();

    $this->withToken($admin->createToken('test-token')->plainTextToken)
        ->getJson('/api/appointments/999')
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});
