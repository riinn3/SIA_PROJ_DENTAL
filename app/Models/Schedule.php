<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment; // <--- This line prevents the crash

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'max_appointments',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Smart Accessor: Counts confirmed appointments for this slot
    public function getBookedCountAttribute()
    {
        return Appointment::whereDate('appointment_date', $this->date)
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    // Link to the User model
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}