<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffExtraHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'route_id',
        'week_number',
        'week_start_date',
        'service_date',
        'start_time',
        'end_time',
        'rate_type',
        'rate_amount',
        'duration_hours',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'service_date' => 'date',
        'duration_hours' => 'float',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function route()
    {
        return $this->belongsTo(StaffRoute::class, 'route_id');
    }
}
