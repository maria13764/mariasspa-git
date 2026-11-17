<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $dashboard = match ($user->role) {
            UserRole::Admin => 'dashboards.admin',
            UserRole::Therapist => 'dashboards.therapist',
            UserRole::Client => 'dashboards.client',
        };

        if ($user->role === UserRole::Client) {
            return view($dashboard, [
                'appointments' => $user->clientAppointments()
                    ->with('therapist:id,name')
                    ->orderByDesc('appointment_at')
                    ->orderByDesc('id')
                    ->paginate(8),
                'appointmentCount' => $user->clientAppointments()->count(),
                'upcomingCount' => $user->clientAppointments()
                    ->whereIn('status', [
                        AppointmentStatus::Pending->value,
                        AppointmentStatus::Confirmed->value,
                    ])
                    ->where('appointment_at', '>=', now())
                    ->count(),
                'pendingCount' => $user->clientAppointments()
                    ->where('status', AppointmentStatus::Pending->value)
                    ->count(),
                'services' => config('spa.services'),
            ]);
        }

        if ($user->role !== UserRole::Admin) {
            return view($dashboard);
        }

        return view($dashboard, [
            'weather' => $this->weather(),
            'weatherLocation' => config('services.weather.city'),
        ]);
    }

    /**
     * @return array{city: string, temperature: float|int, temperature_unit: string, condition: string, humidity: float|int, wind_speed: float|int, wind_speed_unit: string}|null
     */
    private function weather(): ?array
    {
        $apiKey = config('services.weather.api_key');
        $url = config('services.weather.url');
        $city = config('services.weather.city');
        $units = config('services.weather.units');

        if (! is_string($apiKey) || $apiKey === '' || ! is_string($url) || $url === ''
            || ! is_string($city) || $city === '' || ! is_string($units) || $units === '') {
            return null;
        }

        try {
            $payload = Http::timeout(5)
                ->get($url, [
                    'q' => $city,
                    'units' => $units,
                    'lang' => 'en',
                    'appid' => $apiKey,
                ])
                ->throw()
                ->json();
        } catch (ConnectionException|RequestException) {
            return null;
        }

        $city = data_get($payload, 'name');
        $temperature = data_get($payload, 'main.temp');
        $condition = data_get($payload, 'weather.0.description');
        $humidity = data_get($payload, 'main.humidity');
        $windSpeed = data_get($payload, 'wind.speed');

        if (! is_string($city) || ! is_numeric($temperature) || ! is_string($condition)
            || ! is_numeric($humidity) || ! is_numeric($windSpeed)) {
            return null;
        }

        return [
            'city' => $city,
            'temperature' => $temperature,
            'temperature_unit' => match ($units) {
                'imperial' => "\u{00B0}F",
                'standard' => 'K',
                default => "\u{00B0}C",
            },
            'condition' => $condition,
            'humidity' => $humidity,
            'wind_speed' => $windSpeed,
            'wind_speed_unit' => $units === 'imperial' ? 'mph' : 'm/s',
        ];
    }
}
