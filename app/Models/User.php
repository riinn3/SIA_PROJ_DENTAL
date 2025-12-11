<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Helper to check roles easily
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    // Relation for Doctors
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'doctor_id');
    }

    // --- ADD THIS MISSING RELATIONSHIP ---
    // Relation for Patients (and Doctors) to find their bookings
    public function appointments()
    {
        // If the user is a patient, look for 'user_id'
        // If the user is a doctor, look for 'doctor_id'
        if ($this->role === 'doctor') {
            return $this->hasMany(Appointment::class, 'doctor_id');
        }
        
        return $this->hasMany(Appointment::class, 'user_id');
    }
}