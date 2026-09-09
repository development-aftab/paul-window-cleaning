<?php

namespace App\Concerns;

use App\Models\Deposit;
use Carbon\Carbon;

/**
 * Shared logic for resolving the calendar week ("date range") a Deposit covers, used by both
 * the Deposits page (DepositsController) and the weekly bookkeeper email
 * (SendWeeklyDepositReport) so the two stay consistent.
 */
trait ResolvesDepositDateRange
{
    /**
     * Find the portal's 7-day calendar week that a given date falls into, purely from the date
     * itself — the same "first Monday of January + 7-day blocks" tiling the rest of the app uses
     * to lay out weeks, without depending on a (possibly inconsistent) stored week/month string.
     */
    private function calendarWeekRangeForDate(Carbon $date): array
    {
        $firstMonday = Carbon::parse('first Monday of January ' . $date->year);
        if ($date->lt($firstMonday)) {
            $firstMonday = Carbon::parse('first Monday of January ' . ($date->year - 1));
        }

        $weekIndex = intdiv($firstMonday->diffInDays($date), 7);
        $weekStart = $firstMonday->copy()->addWeeks($weekIndex);
        $weekEnd = $weekStart->copy()->addDays(6);

        return ['start' => $weekStart, 'end' => $weekEnd];
    }

    /**
     * Calendar date range for a stored week/month/year triple (e.g. week='week2',
     * month='August - September', year=2026), using the same 4-week cycle-offset map already
     * used for route reports. Returns null when the month string doesn't match a known cycle
     * name (e.g. legacy bare month names like "August").
     */
    private function computeWeekDateRange(?string $week, ?string $month, ?int $year): ?array
    {
        if (!$week || !$year) {
            return null;
        }

        $cycleOffsets = [
            'januaryfebruary' => 0,
            'februarymarch' => 4,
            'march' => 8,
            'marchapril' => 12,
            'aprilmay' => 16,
            'mayjune' => 20,
            'junejuly' => 24,
            'julyaugust' => 28,
            'augustseptember' => 32,
            'septemberoctober' => 36,
            'octobernovember' => 40,
            'novemberdecember' => 44,
            'decemberjanuary' => 48,
        ];

        $normalized = strtolower(preg_replace('/[^a-zA-Z]/', '', (string) $month));
        if (!array_key_exists($normalized, $cycleOffsets)) {
            return null;
        }

        $monthStart = Carbon::parse("first Monday of January {$year}")->addWeeks($cycleOffsets[$normalized]);
        $weekStart = $monthStart->copy()->addDays(($this->weekNumberFromString($week) - 1) * 7);
        $weekEnd = $weekStart->copy()->addDays(6);

        return ['start' => $weekStart, 'end' => $weekEnd];
    }

    private function weekNumberFromString(?string $week): int
    {
        return ((int) preg_replace('/[^0-9]/', '', (string) $week)) + 1;
    }

    /**
     * Format a week date range as e.g. "Aug 03 - 09, 2026" (same month) or
     * "Aug 31 - Sep 06, 2026" (crossing a month boundary).
     */
    private function formatDateLabel(?array $range): string
    {
        if (!$range) {
            return 'N/A';
        }

        $start = $range['start'];
        $end = $range['end'];

        if ($start->format('M') === $end->format('M')) {
            return $start->format('M d') . ' - ' . $end->format('d') . ', ' . $end->format('Y');
        }

        return $start->format('M d') . ' - ' . $end->format('M d') . ', ' . $end->format('Y');
    }

    /**
     * The formatted "date range" label a single Deposit record covers, resolved the same way
     * the Deposits page resolves it: prefer the linked schedule's own date, falling back to the
     * deposit's stored week/month/year triple.
     */
    private function depositDateRangeLabel(Deposit $deposit): string
    {
        $referenceDate = $deposit->schedule_id
            ? ($deposit->clientSchedule?->service_date ?? $deposit->clientSchedule?->start_date)
            : null;

        $range = $referenceDate
            ? $this->calendarWeekRangeForDate(Carbon::parse($referenceDate))
            : $this->computeWeekDateRange($deposit->week, $deposit->month, $deposit->year);

        return $this->formatDateLabel($range);
    }
}
