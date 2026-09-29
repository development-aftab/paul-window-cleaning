<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * PREPAID-SERVICES (simple client wallet)
 *
 * One row = one service a client has already paid for in advance.
 * Created when staff tick "Paid extra for [#] dates" + Amount on the client
 * cash form. Shown in red at the top of Unpaid Accounts, where staff tick
 * each one off after the later cleaning. Old reports are never touched.
 */
class ClientPrepaidService extends Model
{
    protected $table = 'client_prepaid_services';

    protected $guarded = [];

    // This app gives every model a UUID id (see Eloquent Model::boot), so ours is a string too
    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'amount' => 'float',
        'used_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function schedule()
    {
        return $this->belongsTo(ClientSchedule::class, 'schedule_id', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by', 'id');
    }

    /** Same safety net as analytics_history: works even if the migration hasn't been run yet. */
    public static function ensureTable(): void
    {
        if (Schema::hasTable('client_prepaid_services')) {
            // First version of this table had a numeric id, which can't hold this app's UUIDs.
            // Nothing could ever be saved into it, so if it's still empty just rebuild it.
            try {
                $col = \Illuminate\Support\Facades\DB::selectOne(
                    "SELECT DATA_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'client_prepaid_services' AND COLUMN_NAME = 'id'"
                );
                $idType = strtolower($col->t ?? '');
                if ($idType !== '' && !in_array($idType, ['char', 'varchar'], true)
                    && \Illuminate\Support\Facades\DB::table('client_prepaid_services')->count() === 0) {
                    Schema::drop('client_prepaid_services');
                } else {
                    static::ensureUsedPaymentColumn();
                    return;
                }
            } catch (\Throwable $e) {
                return;
            }
        }

        Schema::create('client_prepaid_services', function ($table) {
            $table->char('id', 36)->primary();
            $table->char('client_id', 36)->index();
            $table->char('payment_id', 36)->index();
            $table->char('schedule_id', 36)->nullable();
            $table->char('staff_id', 36)->nullable()->index();
            $table->unsignedSmallInteger('slot_number')->default(1);
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('used_at')->nullable();
            $table->char('used_by', 36)->nullable();
            $table->char('used_payment_id', 36)->nullable();
            $table->timestamps();
        });
    }

    /** Adds the used_payment_id column to a table created before it existed. */
    public static function ensureUsedPaymentColumn(): void
    {
        try {
            if (Schema::hasTable('client_prepaid_services') && !Schema::hasColumn('client_prepaid_services', 'used_payment_id')) {
                Schema::table('client_prepaid_services', function ($table) {
                    $table->char('used_payment_id', 36)->nullable()->after('used_by');
                });
            }
        } catch (\Throwable $e) {
            // not critical - the column is only for reference
        }
    }

    /** Cached map of payment_id => total pre-paid amount collected on that report (for Route Reports). */
    protected static ?array $extraByPayment = null;

    public static function extraForPayment($paymentId): float
    {
        if (!$paymentId) {
            return 0.0;
        }

        if (static::$extraByPayment === null) {
            static::$extraByPayment = [];
            try {
                if (Schema::hasTable('client_prepaid_services')) {
                    static::$extraByPayment = static::query()
                        ->selectRaw('payment_id, SUM(amount) AS total')
                        ->groupBy('payment_id')
                        ->pluck('total', 'payment_id')
                        ->map(fn($v) => (float) $v)
                        ->all();
                }
            } catch (\Throwable $e) {
                static::$extraByPayment = [];
            }
        }

        return (float) (static::$extraByPayment[$paymentId] ?? 0);
    }

    /** "Paid extra for [#] dates" is filled in on this request? Returns [count, amount] or null. */
    protected static function extraPaidFromRequest($request): ?array
    {
        if (($request->payment_type ?? null) !== 'cash') {
            return null;
        }

        $count = (int) ($request->day_number ?? 0);
        $amount = round((float) ($request->amount ?? 0), 2);

        if ($amount <= 0) {
            return null;
        }

        // "#" left empty but an amount was entered -> treat it as 1 pre-paid service
        if ($count < 1) {
            $count = 1;
        }

        return [min($count, 100), $amount];
    }

    /** Create one pending row per pre-paid service, splitting the amount evenly. */
    protected static function createRows(ClientPayment $payment, int $count, float $amount): void
    {
        $each = floor(($amount / $count) * 100) / 100;

        for ($i = 1; $i <= $count; $i++) {
            // last row takes any rounding cents so the rows always add up to the amount
            $slotAmount = $i === $count ? round($amount - ($each * ($count - 1)), 2) : $each;

            static::create([
                'client_id' => $payment->client_id,
                'payment_id' => $payment->id,
                'schedule_id' => $payment->schedule_id,
                'staff_id' => $payment->staff_id,
                'slot_number' => $i,
                'amount' => $slotAmount,
                'status' => 'pending',
            ]);
        }
    }

    /** New report saved (savePayment). */
    public static function recordFromNewPayment(ClientPayment $payment, $request): void
    {
        $extra = static::extraPaidFromRequest($request);
        if (!$extra) {
            return;
        }

        static::ensureTable();
        static::createRows($payment, $extra[0], $extra[1]);
    }

    /**
     * Report edited within the 30-day window (updatePayment).
     * - Report already has pre-paid rows and none are used yet -> rebuild them from the edit (or remove them if "Paid extra" was taken off).
     * - Any row already used -> leave them alone.
     * - Report has no rows yet and "Paid extra" is filled -> create them.
     */
    public static function syncFromEditedPayment(ClientPayment $payment, $request): void
    {
        $extra = static::extraPaidFromRequest($request);
        $existing = Schema::hasTable('client_prepaid_services')
            ? static::where('payment_id', $payment->id)->get()
            : collect();

        if ($existing->isNotEmpty()) {
            if ($existing->contains('status', 'used')) {
                return;
            }
            static::where('payment_id', $payment->id)->delete();
        }

        if ($extra) {
            static::ensureTable();
            static::createRows($payment, $extra[0], $extra[1]);
        }
    }

    /** Cash visit saved with "Paid on prior date of service"? */
    public static function isPriorDateVisit($request): bool
    {
        return ($request->option_two ?? null) === 'paid_on_prior'
            && ($request->payment_type ?? null) === 'cash';
    }

    /** Client's remaining pre-paid balance ($). */
    public static function balanceForClient($clientId): float
    {
        if (!$clientId || !Schema::hasTable('client_prepaid_services')) {
            return 0.0;
        }
        return round((float) static::where('client_id', $clientId)->where('status', 'pending')->sum('amount'), 2);
    }

    /** What this visit is worth - same amount Route Reports uses for Total Sales. */
    public static function visitPrice(ClientPayment $payment): float
    {
        $price = 0.0;
        $schedule = ClientSchedule::find($payment->schedule_id);
        if ($schedule) {
            $price = (float) $schedule->calculateMergedInvoiceAmount();
        }
        if ($price <= 0) {
            $price = (float) ($payment->final_price ?? 0);
        }
        return round($price, 2);
    }

    /**
     * Take $amount from the client's balance (oldest credit first). A credit row bigger than
     * what's needed is split: the used part becomes its own "used" row, the rest stays pending.
     * Returns how much was actually taken (less than $amount if the balance is short).
     */
    protected static function takeFromBalance(ClientPayment $payment, float $amount): float
    {
        $remaining = round($amount, 2);
        $taken = 0.0;
        $usedData = ['status' => 'used', 'used_at' => now(), 'used_by' => auth()->id(), 'used_payment_id' => $payment->id];

        $rows = static::where('client_id', $payment->client_id)
            ->where('status', 'pending')
            ->orderBy('created_at')->orderBy('slot_number')
            ->lockForUpdate()
            ->get();

        foreach ($rows as $row) {
            if ($remaining <= 0) {
                break;
            }
            $rowAmount = round((float) $row->amount, 2);

            if ($rowAmount <= $remaining) {
                $row->update($usedData);
                $remaining = round($remaining - $rowAmount, 2);
                $taken = round($taken + $rowAmount, 2);
            } else {
                $row->update(['amount' => round($rowAmount - $remaining, 2)]);
                static::create(array_merge([
                    'client_id' => $row->client_id,
                    'payment_id' => $row->payment_id,
                    'schedule_id' => $row->schedule_id,
                    'staff_id' => $row->staff_id,
                    'slot_number' => $row->slot_number,
                    'amount' => $remaining,
                ], $usedData));
                $taken = round($taken + $remaining, 2);
                $remaining = 0;
            }
        }

        return $taken;
    }

    /** Give back everything this visit took from the balance. */
    protected static function releaseForPayment(ClientPayment $payment): bool
    {
        $linked = static::where('used_payment_id', $payment->id)->get();
        foreach ($linked as $row) {
            $row->update(['status' => 'pending', 'used_at' => null, 'used_by' => null, 'used_payment_id' => null]);
        }
        return $linked->isNotEmpty();
    }

    /**
     * Settle this visit from the client's pre-paid balance: takes the visit's price from the balance
     * and marks the visit like "Pay with Zelle" - payment_method 'Prepaid' (not cash received in
     * Route Reports) and payment_status 'paid' (never cash to deposit). Total Sales still counts it.
     * Returns the amount taken (0 = no balance, nothing changed).
     */
    public static function settleFromBalance(ClientPayment $payment): float
    {
        if (!Schema::hasTable('client_prepaid_services')) {
            return 0.0;
        }
        static::ensureUsedPaymentColumn();
        if (!Schema::hasColumn('client_prepaid_services', 'used_payment_id')) {
            return 0.0;
        }
        if (static::balanceForClient($payment->client_id) <= 0) {
            return 0.0;
        }

        $price = static::visitPrice($payment);
        if ($price <= 0) {
            return 0.0;
        }

        $taken = 0.0;
        \Illuminate\Support\Facades\DB::transaction(function () use ($payment, $price, &$taken) {
            $taken = static::takeFromBalance($payment, $price);
            $payment->update([
                'status' => 'paid',
                'payment_status' => 'paid',
                'payment_method' => 'Prepaid',
            ]);
        });

        return $taken;
    }

    /** "Paid on prior date of service" saved -> settle from the pre-paid balance automatically. */
    public static function useForPriorDateVisit(ClientPayment $payment): bool
    {
        return static::settleFromBalance($payment) > 0;
    }

    /**
     * Report edited (updatePayment resets status fields): give back what this visit took, then
     * - still "Paid on prior date" (or still settled via "Use Pre-Paid") -> take the (possibly new) price again
     * - changed to anything else -> leave the money in the client's balance
     */
    public static function syncPriorDateOnEdit(ClientPayment $payment, $request): void
    {
        if (!Schema::hasTable('client_prepaid_services')) {
            return;
        }
        static::ensureUsedPaymentColumn();
        if (!Schema::hasColumn('client_prepaid_services', 'used_payment_id')) {
            return;
        }

        $wasLinked = static::where('used_payment_id', $payment->id)->exists();
        $isPrior = static::isPriorDateVisit($request);
        $stillSettled = $isPrior || ($wasLinked && ($request->option ?? null) === 'no_payment');

        if ($wasLinked) {
            static::releaseForPayment($payment);
        }

        if ($stillSettled && static::settleFromBalance($payment->fresh()) > 0) {
            return;
        }

        if ($wasLinked && strtolower((string) $payment->payment_method) === 'prepaid') {
            $payment->update(['payment_method' => null]);
        }
    }
}
