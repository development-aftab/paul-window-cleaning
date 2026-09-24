<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Imported historical sales/hours (e.g. the past 5 years) used by the
 * Analytics page. Live data still comes from the portal itself.
 */
class AnalyticsHistory extends Model
{
    protected $table = 'analytics_history';

    protected $guarded = [];

    protected $casts = [
        'service_date' => 'date',
        'gross_sales' => 'float',
        'hours' => 'float',
    ];
}
