<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function create(): View
    {
        return view('appointments.create', [
            'services' => config('spa.services'),
            'therapists' => User::query()
                ->where('role', UserRole::Therapist->value)
                ->orderBy('name')
                ->get(['id', 'name']),
            'minimumAppointmentAt' => now()->addMinute()->format('Y-m-d\TH:i'),
        ]);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $appointment = Appointment::create([
            ...$request->validated(),
            'client_id' => $request->user()->id,
            'client_name' => $request->user()->name,
            'client_email' => $request->user()->email,
            'status' => AppointmentStatus::Pending,
        ]);

        return redirect()
            ->route('client.appointments.create')
            ->with('booking_confirmation', [
                'reference' => $appointment->id,
                'service' => config("spa.services.{$appointment->service}.name"),
                'appointment_at' => $appointment->appointment_at->format('l, F j, Y \a\t g:i A'),
            ]);
    }

    public function therapist(): View
    {
        return view('appointments.therapist', [
            'appointments' => Appointment::query()
                ->with('therapist:id,name')
                ->orderBy('appointment_at')
                ->orderBy('id')
                ->paginate(15),
            'services' => config('spa.services'),
            'statuses' => AppointmentStatus::cases(),
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([
                    AppointmentStatus::Confirmed->value,
                    AppointmentStatus::Completed->value,
                    AppointmentStatus::Cancelled->value,
                ]),
            ],
        ]);

        $appointment->update([
            'status' => AppointmentStatus::from($validated['status']),
        ]);

        return back()->with('status', 'Appointment status updated.');
    }
}
