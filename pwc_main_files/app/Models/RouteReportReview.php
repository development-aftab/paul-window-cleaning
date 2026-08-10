<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteReportReview extends Model
{
    use HasFactory;

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'route_id',
        'week',
        'month',
        'year',
        'is_reviewed',
        'reviewed_by',
    ];

    protected $casts = [
        'is_reviewed' => 'boolean',
    ];
}
