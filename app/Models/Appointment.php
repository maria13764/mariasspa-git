<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'client_id',
        'client_name',
        'client_phone',
        'client_email',
        'service',
        'therapist_id',
        'appointment_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'appointment_at' => 'datetime',
            'status' => AppointmentStatus::class,
        ];
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
