<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSchedule extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $fillable = [
        'client_id',
        'month',
        'week_month',
        'week',
        'start_date',
        'end_date',
        'payment_type',
        'note',
        'note_two',
        'note_type',
        'note_date',
        'note_week_no',
        'extra_work_price',
        'extra_work',
        'extra_work_price_id',
        'note_two',
        'status',
        'position',
        'is_increase',
        'staff_id',
        'priority'
    ];

    public function clientSchedulePrice()
    {
        return $this->hasMany(ClientSchedulePrice::class, 'schedule_id', 'id');
    }

    public function clientSchedulePayment()
    {
        return $this->hasOne(ClientPayment::class, 'schedule_id', 'id');
    }

    public function clientName()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function clientHour()
    {
        return $this->hasMany(ClientTime::class, 'client_id', 'client_id');
    }

    public function StaffName()
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }

    public function calculateMergedInvoiceAmount(): float
    {
        $groupSchedules = self::with(['clientSchedulePrice.clientPaymentPrice', 'clientSchedulePayment'])
            ->where('client_id', $this->client_id)
            ->where('start_date', $this->start_date)
            ->get();

        $mergedInvoiceAmount = 0;

        foreach ($groupSchedules as $sch) {
            if ($sch->clientSchedulePrice && $sch->clientSchedulePrice->count() > 0) {
                foreach ($sch->clientSchedulePrice as $sp) {
                    $mergedInvoiceAmount += (float) (optional($sp->clientPaymentPrice)->value ?? 0);
                }
            }

            if ($sch->extra_work && $sch->extra_work_price) {
                $names = json_decode($sch->extra_work, true);
                $values = json_decode($sch->extra_work_price, true);

                if (is_array($names) && is_array($values)) {
                    foreach ($names as $idx => $name) {
                        $mergedInvoiceAmount += (float) ($values[$idx] ?? 0);
                    }
                }
            }

            $mergedInvoiceAmount += (float) (optional($sch->clientSchedulePayment)->price_charge_two ?? 0);
        }

        return $mergedInvoiceAmount;
    }

    public function calculateMergedScope(): array
    {
        $groupSchedules = self::with(['clientSchedulePrice.clientPaymentPrice', 'clientSchedulePayment'])
            ->where('client_id', $this->client_id)
            ->where('start_date', $this->start_date)
            ->get();

        $scopeNames = [];
        $extraWorkNames = [];

        foreach ($groupSchedules as $sch) {
            if ($sch->clientSchedulePrice && $sch->clientSchedulePrice->count() > 0) {
                foreach ($sch->clientSchedulePrice as $sp) {
                    $name = optional($sp->clientPaymentPrice)->name;
                    if (!empty($name)) {
                        $scopeNames[] = $name;
                    }
                }
            }

            if ($sch->extra_work) {
                $extraNames = json_decode($sch->extra_work, true);
                if (is_array($extraNames)) {
                    foreach ($extraNames as $name) {
                        if (!empty($name)) {
                            $scopeNames[] = $name;
                        }
                    }
                }
            }

            $paymentScope = trim((string) optional($sch->clientSchedulePayment)->scope);
            if ($paymentScope !== '') {
                $extraWorkNames[] = $paymentScope;
            }
        }

        return [
            'scope' => array_values(array_unique($scopeNames)),
            'extra_work' => array_values(array_unique($extraWorkNames)),
        ];
    }

}
