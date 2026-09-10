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
                if (isset($sch->clientSchedulePayment) && $sch->clientSchedulePayment->option_five == 'partially'){
                    $mergedInvoiceAmount += (float) (optional($sch->clientSchedulePayment)->price_charge_one ?? 0);
                    continue;
                }
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

    /**
     * The price_ids checked off across every ClientSchedule row sharing this same client_id +
     * start_date. A single client+date can have more than one ClientSchedule row (e.g. a note
     * added on a separate row), and the row a page happens to load via ->first() may not be the
     * one carrying the clientSchedulePrice records — so scope checkboxes must check membership
     * across the whole group, not just $this->clientSchedulePrice.
     */
    public function calculateMergedPriceIds(): array
    {
        $groupSchedules = self::with('clientSchedulePrice')
            ->where('client_id', $this->client_id)
            ->where('start_date', $this->start_date)
            ->get();

        $priceIds = $groupSchedules
            ->flatMap(fn ($sch) => $sch->clientSchedulePrice->pluck('price_id'))
            ->all();

        // "Extra Work Completed" checkboxes save the selected item's *name* into extra_work
        // (no price_id link), so resolve those names back to their price_id here too — otherwise
        // an item added that way never shows as checked in the scope-of-work list.
        $extraNames = $groupSchedules
            ->flatMap(function ($sch) {
                $names = json_decode($sch->extra_work ?? '', true);
                return is_array($names) ? $names : [];
            })
            ->filter()
            ->unique();

        if ($extraNames->isNotEmpty()) {
            $resolvedIds = ClientPriceList::where('client_id', $this->client_id)
                ->whereIn('name', $extraNames)
                ->pluck('id');
            $priceIds = array_merge($priceIds, $resolvedIds->all());
        }

        return array_values(array_unique($priceIds));
    }

    public function calculateMergedScope(): array
    {
        $groupSchedules = self::with(['clientSchedulePrice.clientPaymentPrice', 'clientSchedulePayment'])
            ->where('client_id', $this->client_id)
            ->where('start_date', $this->start_date)
            ->get();

        $scopeItems = [];
        $extraWorkItems = [];

        foreach ($groupSchedules as $sch) {
            if ($sch->clientSchedulePrice && $sch->clientSchedulePrice->count() > 0) {
                foreach ($sch->clientSchedulePrice as $sp) {
                    $name = optional($sp->clientPaymentPrice)->name;
                    if (!empty($name)) {
                        $scopeItems[$name] = (float) (optional($sp->clientPaymentPrice)->value ?? 0);
                    }
                }
            }

            if ($sch->extra_work) {
                $extraNames = json_decode($sch->extra_work, true);
                $extraValues = json_decode($sch->extra_work_price, true);
                if (is_array($extraNames)) {
                    foreach ($extraNames as $idx => $name) {
                        if (!empty($name)) {
                            $scopeItems[$name] = (float) ($extraValues[$idx] ?? 0);
                        }
                    }
                }
            }

            $paymentScope = trim((string) optional($sch->clientSchedulePayment)->scope);
            if ($paymentScope !== '') {
                $extraWorkItems[$paymentScope] = (float) (optional($sch->clientSchedulePayment)->price_charge_two ?? 0);
            }
        }

        $toItemList = function (array $items): array {
            $list = [];
            foreach ($items as $name => $value) {
                $list[] = ['name' => $name, 'value' => $value];
            }
            return $list;
        };

        return [
            'scope' => $toItemList($scopeItems),
            'extra_work' => $toItemList($extraWorkItems),
        ];
    }

}
