<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'specialty',
        'license_number',
        'bio',
        'phone',
        'email',
        'photo',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($doctor) {
            if (empty($doctor->slug)) {
                $doctor->slug = Str::slug($doctor->name);
            }
        });

        static::updating(function ($doctor) {
            if ($doctor->isDirty('name')) {
                $doctor->slug = Str::slug($doctor->name);
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
