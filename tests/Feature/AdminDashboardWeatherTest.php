<?php

use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('admin dashboard loads weather for the configured location and units', function () {
    config([
        'services.weather.url' => 'https://weather.example.test/current',
        'services.weather.api_key' => 'test-weather-key',
        'services.weather.city' => 'Davao City, Davao del Sur, Philippines',
        'services.weather.units' => 'metric',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'weather.example.test/current*' => Http::response([
            'name' => 'Davao City',
            'main' => [
                'temp' => 29.4,
                'humidity' => 76,
            ],
            'weather' => [
                ['description' => 'light rain'],
            ],
            'wind' => [
                'speed' => 2.8,
            ],
        ]),
    ]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Weather in Davao City, Davao del Sur, Philippines')
        ->assertSee('29.4')
        ->assertSee("\u{00B0}C")
        ->assertSee('light rain')
        ->assertSee('76%')
        ->assertSee('2.8 m/s');

    Http::assertSent(fn (Request $request): bool => str_starts_with($request->url(), 'https://weather.example.test/current')
        && $request->data() === [
            'q' => 'Davao City, Davao del Sur, Philippines',
            'units' => 'metric',
            'lang' => 'en',
            'appid' => 'test-weather-key',
        ]
    );
});

test('admin dashboard stays available when the weather request cannot connect', function () {
    config(['services.weather.api_key' => 'test-weather-key']);
    Http::preventStrayRequests();
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::failedConnection(),
    ]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Weather is currently unavailable.')
        ->assertSee('Manage users');
});

test('admin dashboard stays available when the weather API returns an error', function () {
    config(['services.weather.api_key' => 'test-weather-key']);
    Http::preventStrayRequests();
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::response(['message' => 'service unavailable'], 503),
    ]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Weather is currently unavailable.')
        ->assertSee('Manage users');
});

test('admin dashboard shows the weather warning when the API key is not configured', function () {
    config(['services.weather.api_key' => null]);
    Http::preventStrayRequests();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Weather is currently unavailable.')
        ->assertSee('Manage users');
});
