<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function index(): JsonResponse
    {
        $appointments = Appointment::query()
            ->orderByDesc('appointment_at')
            ->paginate(15);

        return response()->json([
            'data' => $appointments->items(),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $input = $this->appointmentInput($request->all());

        if (array_key_exists('duplicate_id', $input)) {
            $validated = Validator::make($input, [
                'duplicate_id' => ['required', 'integer', 'exists:appointments,id'],
            ])->validate();
            $appointment = Appointment::query()->findOrFail($validated['duplicate_id']);
            $duplicate = $appointment->replicate(['id', 'created_at', 'updated_at']);
            $duplicate->save();

            return response()->json([
                'message' => 'Appointment duplicated successfully.',
                'data' => $duplicate,
            ], 201);
        }

        $validated = Validator::make($input, $this->rules())->validate();
        $validated['status'] ??= AppointmentStatus::Pending->value;
        $appointment = Appointment::query()->create($validated);

        return response()->json([
            'message' => 'Appointment created successfully.',
            'data' => $appointment,
        ], 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        return response()->json([
            'data' => $appointment,
        ]);
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $input = $this->appointmentInput($request->all());
        $partial = $request->isMethod('PATCH');
        $validated = Validator::make($input, $this->rules($partial))->validate();

        if (! $partial) {
            $validated['client_id'] ??= null;
            $validated['client_email'] ??= null;
            $validated['therapist_id'] ??= null;
            $validated['status'] ??= AppointmentStatus::Pending->value;
            $validated['notes'] ??= null;
        }

        if (! $appointment->update($validated)) {
            return response()->json([
                'message' => 'Appointment could not be updated.',
            ], 500);
        }

        return response()->json([
            'message' => 'Appointment updated successfully.',
            'data' => $appointment->refresh(),
        ]);
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $deletedAppointment = $appointment->toArray();

        if (! $appointment->delete()) {
            return response()->json([
                'message' => 'Appointment could not be deleted.',
            ], 500);
        }

        return response()->json([
            'message' => 'Appointment deleted successfully. This appointment has been permanently removed.',
            'data' => $deletedAppointment,
        ]);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function appointmentInput(array $input): array
    {
        if (array_is_list($input)) {
            $input = $input[0] ?? [];
        }

        if (! is_array($input)) {
            return [];
        }

        if (! isset($input['data']) || ! is_array($input['data'])) {
            return $input;
        }

        $data = $input['data'];

        if (array_is_list($data)) {
            $data = $data[0] ?? [];
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    private function rules(bool $partial = false): array
    {
        $presence = $partial ? 'sometimes' : 'required';

        return [
            'client_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::Client->value),
            ],
            'client_name' => [$presence, 'string', 'max:255'],
            'client_phone' => [$presence, 'string', 'max:30'],
            'client_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'service' => [
                $presence,
                'string',
                Rule::in(array_keys(config('spa.services'))),
            ],
            'therapist_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::Therapist->value),
            ],
            'appointment_at' => [
                $presence,
                'date',
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::in(array_map(
                    static fn (AppointmentStatus $status): string => $status->value,
                    AppointmentStatus::cases(),
                )),
            ],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
