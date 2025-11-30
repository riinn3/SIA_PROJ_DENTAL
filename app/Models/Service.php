<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    // these are the only fields we allow to be mass-assigned
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
        'status',
    ];
}