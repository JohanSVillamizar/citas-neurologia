<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'doctor_id',
        'patient_name',
        'patient_email',
        'patient_phone',
        'patient_id_number',
        'patient_birth_date',
        'reason',
        'appointment_date',
        'duration_minutes',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'patient_birth_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (empty($appointment->slug)) {
                $appointment->slug = Str::random(10);
            }
        });
    }

    public function getRouteKeyName()
    {
        return request()->is('appointments*') ? 'id' : 'slug';
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
