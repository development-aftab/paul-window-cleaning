<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'staff_id',
        'schedule_id',
        'client_payment_id',
        'week',
        'month',
        'year',
        'total_amount',
        'deposit_amount',
        'is_deposit',
        'deposit_date',
        'payment_type',
        'notes',
    ];

    protected $casts = [
        'is_deposit' => 'boolean',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'deposit_date' => 'date',
    ];

    /**
     * Get the route that owns the deposit.
     */
    public function route()
    {
        return $this->belongsTo(StaffRoute::class, 'route_id');
    }

    /**
     * Get the staff user that owns the deposit.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function clientSchedule()
    {
        return $this->belongsTo(ClientSchedule::class, 'schedule_id');
    }

    public function clientPayment()
    {
        return $this->belongsTo(ClientPayment::class, 'client_payment_id');
    }
}

