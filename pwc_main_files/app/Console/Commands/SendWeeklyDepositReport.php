<?php

namespace App\Console\Commands;

use App\Concerns\ResolvesDepositDateRange;
use App\Mail\WeeklyDepositReportMail;
use App\Models\Deposit;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDepositReport extends Command
{
    use ResolvesDepositDateRange;

    protected $signature = 'deposits:send-weekly-report';
    protected $description = 'Email the bookkeeper the week\'s deposits (Monday through Saturday)';

    public function handle()
    {
        $now = Carbon::now('America/Chicago');

        // Run is scheduled for Sunday night, so "that week" is the Monday-Saturday block that
        // just finished — startOfWeek(MONDAY) on a Sunday rolls back to that Monday.
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(5); // Saturday

        $this->info("Building deposit report for {$weekStart->toDateString()} - {$weekEnd->toDateString()}...");

        $deposits = Deposit::with(['staff', 'clientSchedule'])
            ->whereBetween('deposit_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $rows = $deposits
            ->map(function (Deposit $deposit) {
                return [
                    'staff_name' => $deposit->staff->name ?? 'Unassigned',
                    'date_range' => $this->depositDateRangeLabel($deposit),
                    'deposit_date' => $deposit->deposit_date ? $deposit->deposit_date->format('m/d/Y') : 'N/A',
                    'amount' => (float) $deposit->deposit_amount,
                    'sort_date' => $deposit->deposit_date,
                ];
            })
            ->sortBy([
                ['staff_name', 'asc'],
                ['sort_date', 'asc'],
            ])
            ->values();

        $data = [
            'period_label' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y'),
            'rows' => $rows,
            'total' => $rows->sum('amount'),
        ];

        $bookkeeperEmail = env('BOOKKEEPER_EMAIL', 'ray@wiseeyesbookkeeping.com');

        try {
            Mail::to($bookkeeperEmail)->cc('shayankhan.be.tafsol@gmail.com')->send(new WeeklyDepositReportMail($data));
            $this->info("Weekly deposit report emailed to {$bookkeeperEmail} ({$rows->count()} entries).");
        } catch (\Exception $e) {
            Log::error('Failed to send weekly deposit report: ' . $e->getMessage());
            $this->error('Failed to send weekly deposit report: ' . $e->getMessage());
        }
    }
}
