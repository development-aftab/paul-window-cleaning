<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsHistory;
use App\Models\ClientSchedule;
use App\Models\StaffLogHour;
use App\Models\StaffRoute;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Analytics: gross commercial sales, hours and $/hour, broken down by week,
 * route, cycle, year or account. Live data comes from the portal (same rules
 * as the Route Report); older years come from imported history.
 */
class AnalyticsController extends Controller
{
    /** The 13 four-week cycles of the year (same names the Route Report uses). */
    private const CYCLE_NAMES = [
        'January - February', 'February - March', 'March', 'March - April',
        'April - May', 'May - June', 'June - July', 'July - August',
        'August - September', 'September - October', 'October - November',
        'November - December', 'December - January',
    ];

    private const GROUPS = ['route', 'week', 'calendar_week', 'cycle', 'year', 'route_week'];

    // ------------------------------------------------------------------ pages

    public function index()
    {
        $this->authorizeAdmin();
        $this->ensureHistoryTable();

        $routes = StaffRoute::where('status', 1)->orderBy('name')->get(['id', 'name']);

        $liveNames = $routes->pluck('name')->map(fn ($n) => $this->key($n))->all();
        $historyRoutes = AnalyticsHistory::select('route_name', 'route_key')
            ->distinct()->orderBy('route_name')->get()
            ->filter(fn ($r) => !in_array($r->route_key, $liveNames, true))
            ->unique('route_key')->values();

        $batches = AnalyticsHistory::selectRaw('import_batch, COUNT(*) as rows_count, MIN(service_date) as first_date, MAX(service_date) as last_date, MAX(created_at) as imported_at')
            ->groupBy('import_batch')->orderByDesc('imported_at')->get();

        return view('dashboard.analytics.index', [
            'routes' => $routes,
            'historyRoutes' => $historyRoutes,
            'batches' => $batches,
            'defaultFrom' => now()->startOfYear()->toDateString(),
            'defaultTo' => now()->toDateString(),
        ]);
    }

    /** JSON: grouped rows, totals and per-account hours for the chosen filters. */
    public function data(Request $request)
    {
        $this->authorizeAdmin();
        $this->ensureHistoryTable();

        $f = $this->filters($request);
        $built = $this->buildFacts($f);

        return response()->json([
            'filters' => [
                'from' => $f['from']->toDateString(),
                'to' => $f['to']->toDateString(),
                'weeks' => $f['weeks'],
                'group_by' => $f['group'],
                'hours_basis' => $f['basis'],
            ],
            'summary' => $this->summarize($built, $f),
            'accounts' => $this->accountRows($built),
            'notes' => $built['notes'],
        ]);
    }

    // ----------------------------------------------------------------- import

    public function template()
    {
        $this->authorizeAdmin();

        $csv = "date,route,account,sales,hours,week,client_type\n"
             . "2021-03-08,Evanston,Sample Office Park,450.00,3.5,1,commercial\n"
             . "03/15/2021,Schaumburg,Sample Bank Branch,275.50,2,2,commercial\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics_history_template.csv"',
        ]);
    }

    public function import(Request $request)
    {
        $this->authorizeAdmin();
        $this->ensureHistoryTable();

        $request->validate(['file' => 'required|file|mimes:csv,txt|max:20480']);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('analytics_status', ['type' => 'error', 'text' => 'The file could not be read.']);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('analytics_status', ['type' => 'error', 'text' => 'The file is empty.']);
        }

        $aliases = [
            'date' => ['date', 'service_date', 'day', 'service date'],
            'route' => ['route', 'route_name', 'route name'],
            'account' => ['account', 'account_name', 'client', 'customer', 'account name'],
            'sales' => ['sales', 'gross', 'gross_sales', 'gross sales', 'amount', 'revenue', 'total'],
            'hours' => ['hours', 'hrs', 'hr', 'duration'],
            'week' => ['week', 'week_number', 'week number', 'wk'],
            'type' => ['client_type', 'type', 'client type'],
        ];

        $map = [];
        foreach ($header as $i => $name) {
            $name = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $name)));
            foreach ($aliases as $field => $names) {
                if (in_array($name, $names, true) && !isset($map[$field])) {
                    $map[$field] = $i;
                }
            }
        }

        foreach (['date', 'route', 'sales'] as $required) {
            if (!isset($map[$required])) {
                fclose($handle);
                return back()->with('analytics_status', [
                    'type' => 'error',
                    'text' => "Missing required column \"{$required}\". Required: date, route, sales. Optional: account, hours, week, client_type.",
                ]);
            }
        }

        $batch = 'import-' . now()->format('Ymd-His');
        $now = now();
        $buffer = [];
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $date = $this->parseDate($row[$map['date']] ?? null);
            $route = trim((string) ($row[$map['route']] ?? ''));
            $sales = $this->number($row[$map['sales']] ?? null);

            if (!$date || $route === '' || $sales === null) {
                $skipped++;
                if (count($errors) < 5) {
                    $errors[] = "line {$line}";
                }
                continue;
            }

            $week = isset($map['week']) ? (int) ($row[$map['week']] ?? 0) : 0;
            if ($week < 1 || $week > 4) {
                $week = $this->cycleInfo($date)['week'];
            }

            $type = isset($map['type']) ? strtolower(trim((string) ($row[$map['type']] ?? ''))) : '';
            $account = isset($map['account']) ? trim((string) ($row[$map['account']] ?? '')) : '';

            $buffer[] = [
                'service_date' => $date->toDateString(),
                'week_number' => $week,
                'route_name' => $route,
                'route_key' => $this->key($route),
                'account_name' => $account !== '' ? $account : null,
                'client_type' => $type !== '' ? $type : 'commercial',
                'gross_sales' => $sales,
                'hours' => isset($map['hours']) ? ($this->number($row[$map['hours']] ?? null) ?? 0) : 0,
                'import_batch' => $batch,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $imported++;

            if (count($buffer) >= 500) {
                AnalyticsHistory::insert($buffer);
                $buffer = [];
            }
        }
        fclose($handle);
        if ($buffer) {
            AnalyticsHistory::insert($buffer);
        }

        $text = "Imported {$imported} row(s) as batch {$batch}.";
        if ($skipped) {
            $text .= " Skipped {$skipped} row(s) with a missing/invalid date, route or sales value ("
                . implode(', ', $errors) . ($skipped > count($errors) ? ', ...' : '') . ').';
        }

        return back()->with('analytics_status', ['type' => $imported ? 'success' : 'error', 'text' => $text]);
    }

    public function deleteBatch(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate(['batch' => 'required|string|max:60']);

        $count = AnalyticsHistory::where('import_batch', $request->input('batch'))->delete();

        return back()->with('analytics_status', ['type' => 'success', 'text' => "Removed {$count} imported row(s)."]);
    }

    // ------------------------------------------------------------ data engine

    /**
     * Facts are simple records the summary/account views aggregate:
     *  kind = sale   (sales + jobs [+ job hours])   from live completed commercial jobs
     *  kind = logged (logged route hours only)       from staff hour logs
     *  kind = hist   (sales + hours [+ account])     from imported history
     */
    private function buildFacts(array $f): array
    {
        $facts = [];
        $notes = [];

        // Which live routes / history route names are selected (empty = all).
        $liveRouteIds = [];
        $historyOnly = [];
        foreach ($f['routes'] as $value) {
            if (strpos($value, 'h:') === 0) {
                $historyOnly[] = $this->key(substr($value, 2));
            } else {
                $liveRouteIds[] = $value;
            }
        }
        $filterByRoute = !empty($f['routes']);
        $historyKeys = $historyOnly;
        if ($liveRouteIds) {
            foreach (StaffRoute::whereIn('id', $liveRouteIds)->pluck('name') as $n) {
                $historyKeys[] = $this->key($n);
            }
        }
        $historyKeys = array_values(array_unique($historyKeys));

        $fromStr = $f['from']->toDateString();
        $toStr = $f['to']->toDateString();

        // ---- live sales (same rules as the Route Report) ----
        if (!$filterByRoute || $liveRouteIds) {
            $q = ClientSchedule::where('status', 'completed')
                ->whereBetween('start_date', [$fromStr, $toStr])
                ->whereHas('clientName', fn ($c) => $c->where('client_type', 'commercial'))
                ->whereDoesntHave('StaffName.profile', fn ($p) => $p->where('employment_type', 'subcontractor'))
                ->with(['clientSchedulePayment', 'clientName.clientRouteStaff.route']);

            if ($liveRouteIds) {
                $q->whereHas('clientName.clientRouteStaff', fn ($c) => $c->whereIn('route_id', $liveRouteIds));
            }

            $rows = $q->get();

            // All rows of each client + date (any status) so merged invoice amounts match the report.
            $groups = collect();
            foreach (array_chunk($rows->pluck('client_id')->unique()->values()->all(), 800) as $chunk) {
                $groups = $groups->concat(
                    ClientSchedule::with(['clientSchedulePrice.clientPaymentPrice', 'clientSchedulePayment'])
                        ->whereIn('client_id', $chunk)
                        ->whereBetween('start_date', [$fromStr, $toStr])
                        ->get()
                );
            }
            $groups = $groups->groupBy(fn ($s) => $s->client_id . '_' . $s->start_date);

            $merged = $rows->groupBy(fn ($s) => $s->client_id . '_' . $s->start_date)
                ->map(fn ($g) => $g->first(fn ($s) => $s->clientSchedulePayment !== null) ?? $g->first());

            foreach ($merged as $key => $sch) {
                $date = $this->parseDate($sch->start_date);
                if (!$date) {
                    continue;
                }
                $info = $this->cycleInfo($date);
                if ($f['weeks'] && !in_array($info['week'], $f['weeks'], true)) {
                    continue;
                }

                $group = $groups->get($key, collect([$sch]));
                $amount = $sch->calculateMergedInvoiceAmount($group);
                if (!$amount) {
                    $amount = (float) ($sch->clientSchedulePayment->final_price ?? 0);
                }

                $route = $sch->clientName?->clientRouteStaff->first()?->route;
                $routeName = $route->name ?? 'No route';

                $facts[] = [
                    'kind' => 'sale',
                    'date' => $date,
                    'week' => $info['week'],
                    'route_key' => $this->key($routeName),
                    'route' => $routeName,
                    'account' => $sch->clientName->name ?? 'Unknown account',
                    'sales' => (float) $amount,
                    'jobs' => 1,
                    'job_hours' => $this->jobHours($sch->clientSchedulePayment),
                ];
            }
        }

        // ---- live logged route hours ----
        if ($f['basis'] === 'logged' && (!$filterByRoute || $liveRouteIds)) {
            $h = StaffLogHour::with('route')->whereBetween('service_date', [$fromStr, $toStr]);
            if ($liveRouteIds) {
                $h->whereIn('route_id', $liveRouteIds);
            }
            foreach ($h->get() as $log) {
                $date = $log->service_date ? Carbon::parse($log->service_date) : null;
                if (!$date) {
                    continue;
                }
                $info = $this->cycleInfo($date);
                if ($f['weeks'] && !in_array($info['week'], $f['weeks'], true)) {
                    continue;
                }
                $routeName = $log->route->name ?? 'No route';
                $facts[] = [
                    'kind' => 'logged',
                    'date' => $date,
                    'week' => $info['week'],
                    'route_key' => $this->key($routeName),
                    'route' => $routeName,
                    'account' => null,
                    'sales' => 0.0,
                    'jobs' => 0,
                    'logged_hours' => (float) $log->duration_hours,
                ];
            }
        }

        // ---- imported history ----
        if ($f['history'] && (!$filterByRoute || $historyKeys)) {
            $liveStart = ClientSchedule::where('status', 'completed')->min('start_date');

            $h = AnalyticsHistory::where('client_type', 'commercial')->whereBetween('service_date', [$fromStr, $toStr]);
            if ($liveStart) {
                // Live data takes over from its first completed job; avoids double counting.
                $h->where('service_date', '<', $liveStart);
                $notes[] = 'Imported history is used for dates before ' . Carbon::parse($liveStart)->format('M j, Y')
                    . '; live portal data is used from then on.';
            }
            if ($filterByRoute) {
                $h->whereIn('route_key', $historyKeys);
            }
            foreach ($h->get() as $row) {
                $date = Carbon::parse($row->service_date);
                $week = $row->week_number ?: $this->cycleInfo($date)['week'];
                if ($f['weeks'] && !in_array((int) $week, $f['weeks'], true)) {
                    continue;
                }
                $facts[] = [
                    'kind' => 'hist',
                    'date' => $date,
                    'week' => (int) $week,
                    'route_key' => $row->route_key,
                    'route' => $row->route_name,
                    'account' => $row->account_name,
                    'sales' => (float) $row->gross_sales,
                    'jobs' => $row->account_name ? 1 : 0,
                    'hist_hours' => (float) $row->hours,
                ];
            }
        }

        $notes[] = $f['basis'] === 'jobs'
            ? 'Hours are taken from the start/end times recorded on each completed commercial job.'
            : 'Hours are the logged route hours (same source as the Route Report HRs) and cover all work on the route, not only commercial jobs.';

        return ['facts' => $facts, 'notes' => $notes];
    }

    private function hoursOf(array $fact, string $basis): float
    {
        if ($fact['kind'] === 'hist') {
            return $fact['hist_hours'];
        }
        if ($fact['kind'] === 'logged') {
            return $basis === 'logged' ? $fact['logged_hours'] : 0.0;
        }

        return $basis === 'jobs' ? (float) ($fact['job_hours'] ?? 0) : 0.0;
    }

    /** @return array{0:string,1:string,2:mixed} key, label, sort value */
    private function groupOf(array $fact, string $group): array
    {
        $date = $fact['date'];

        if ($group === 'week') {
            return [(string) $fact['week'], 'Week ' . $fact['week'], $fact['week']];
        }
        if ($group === 'calendar_week') {
            $monday = $date->copy()->startOfWeek(Carbon::MONDAY);
            return [$monday->toDateString(), 'Week of ' . $monday->format('M j, Y'), $monday->toDateString()];
        }
        if ($group === 'cycle' || $group === 'year') {
            $info = $this->cycleInfo($date);
            if ($group === 'year') {
                return [(string) $info['year'], (string) $info['year'], $info['year']];
            }
            $key = $info['year'] . '-' . str_pad((string) $info['cycle'], 2, '0', STR_PAD_LEFT);
            return [$key, self::CYCLE_NAMES[$info['cycle'] - 1] . ' ' . $info['year'], $key];
        }
        if ($group === 'route_week') {
            $key = $fact['route_key'] . '|' . $fact['week'];
            return [$key, $fact['route'] . ' - Week ' . $fact['week'], $key];
        }

        return [$fact['route_key'], $fact['route'], $fact['route_key']];
    }

    private function summarize(array $built, array $f): array
    {
        $rows = [];
        $total = ['sales' => 0.0, 'hours' => 0.0, 'jobs' => 0];

        foreach ($built['facts'] as $fact) {
            [$key, $label, $sort] = $this->groupOf($fact, $f['group']);
            $hours = $this->hoursOf($fact, $f['basis']);

            if (!isset($rows[$key])) {
                $rows[$key] = ['key' => $key, 'label' => $label, 'sort' => $sort, 'sales' => 0.0, 'hours' => 0.0, 'jobs' => 0];
            }
            $rows[$key]['sales'] += $fact['sales'];
            $rows[$key]['hours'] += $hours;
            $rows[$key]['jobs'] += $fact['jobs'];

            $total['sales'] += $fact['sales'];
            $total['hours'] += $hours;
            $total['jobs'] += $fact['jobs'];
        }

        $chronological = in_array($f['group'], ['week', 'calendar_week', 'cycle', 'year'], true);
        $rows = collect($rows)->map(function ($r) {
            $r['sales'] = round($r['sales'], 2);
            $r['hours'] = round($r['hours'], 2);
            $r['per_hour'] = $r['hours'] > 0 ? round($r['sales'] / $r['hours'], 2) : null;
            return $r;
        });
        $rows = ($chronological ? $rows->sortBy('sort') : $rows->sortByDesc('sales'))->values();

        return [
            'rows' => $rows->map(function ($r) {
                unset($r['sort']);
                return $r;
            })->all(),
            'totals' => [
                'sales' => round($total['sales'], 2),
                'hours' => round($total['hours'], 2),
                'per_hour' => $total['hours'] > 0 ? round($total['sales'] / $total['hours'], 2) : null,
                'jobs' => $total['jobs'],
            ],
        ];
    }

    /** Hours each account takes to service (job start/end times + imported hours). */
    private function accountRows(array $built): array
    {
        $accounts = [];

        foreach ($built['facts'] as $fact) {
            if (!in_array($fact['kind'], ['sale', 'hist'], true) || empty($fact['account'])) {
                continue;
            }
            $hours = $fact['kind'] === 'hist' ? $fact['hist_hours'] : ($fact['job_hours'] ?? null);
            $key = mb_strtolower($fact['account']) . '|' . $fact['route_key'];

            if (!isset($accounts[$key])) {
                $accounts[$key] = [
                    'account' => $fact['account'], 'route' => $fact['route'],
                    'visits' => 0, 'timed_visits' => 0, 'hours' => 0.0, 'sales' => 0.0,
                ];
            }
            $accounts[$key]['visits']++;
            $accounts[$key]['sales'] += $fact['sales'];
            if ($hours !== null && $hours > 0) {
                $accounts[$key]['timed_visits']++;
                $accounts[$key]['hours'] += $hours;
            }
        }

        return collect($accounts)->map(function ($a) {
            $a['hours'] = round($a['hours'], 2);
            $a['sales'] = round($a['sales'], 2);
            $a['avg_hours'] = $a['timed_visits'] > 0 ? round($a['hours'] / $a['timed_visits'], 2) : null;
            $a['per_hour'] = $a['hours'] > 0 ? round($a['sales'] / $a['hours'], 2) : null;
            return $a;
        })->sortByDesc('hours')->values()->all();
    }

    // ----------------------------------------------------------------- helpers

    private function filters(Request $r): array
    {
        $from = $this->parseDate($r->input('from')) ?? now()->startOfYear();
        $to = $this->parseDate($r->input('to')) ?? now();
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $weeks = collect((array) $r->input('weeks', []))
            ->map(fn ($w) => (int) $w)->filter(fn ($w) => $w >= 1 && $w <= 4)->unique()->sort()->values()->all();

        $routes = collect((array) $r->input('routes', []))
            ->map(fn ($v) => trim((string) $v))->filter(fn ($v) => $v !== '' && $v !== 'all')->unique()->values()->all();

        $group = in_array($r->input('group_by'), self::GROUPS, true) ? $r->input('group_by') : 'route';

        return [
            'from' => $from->copy()->startOfDay(),
            'to' => $to->copy()->endOfDay(),
            'weeks' => $weeks,
            'routes' => $routes,
            'group' => $group,
            'basis' => $r->input('hours_basis') === 'jobs' ? 'jobs' : 'logged',
            'history' => $r->boolean('include_history', true),
        ];
    }

    /** Year / 4-week cycle (1-13) / week-in-cycle (1-4) for a date, matching the Route Report calendar. */
    private function cycleInfo(Carbon $date): array
    {
        $date = $date->copy()->startOfDay();
        $year = $date->year;
        $base = Carbon::parse("first Monday of January $year")->startOfDay();
        if ($date->lt($base)) {
            $year--;
            $base = Carbon::parse("first Monday of January $year")->startOfDay();
        }

        $index = (int) $base->diffInDays($date);
        $cycle = min(intdiv($index, 28), 12);
        $week = min(intdiv($index - $cycle * 28, 7) + 1, 4);

        return ['year' => $year, 'cycle' => $cycle + 1, 'week' => $week];
    }

    private function jobHours($payment): ?float
    {
        if (!$payment || !$payment->start_time || !$payment->end_time) {
            return null;
        }
        try {
            $start = Carbon::parse($payment->start_time);
            $end = Carbon::parse($payment->end_time);
        } catch (\Throwable $ex) {
            return null;
        }

        $minutes = (int) $start->diffInMinutes($end, false);
        if ($minutes < 0) {
            $minutes += 1440;       // finished after midnight
        }

        return ($minutes > 0 && $minutes <= 1440) ? round($minutes / 60, 2) : null;
    }

    private function parseDate($value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        try {
            if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{2,4})$#', $value, $m)) {
                $year = (int) $m[3] < 100 ? 2000 + (int) $m[3] : (int) $m[3];
                return Carbon::createFromDate($year, (int) $m[1], (int) $m[2])->startOfDay();
            }
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $ex) {
            return null;
        }
    }

    private function number($value): ?float
    {
        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return ($clean === '' || !is_numeric($clean)) ? null : (float) $clean;
    }

    private function key($name): string
    {
        return mb_strtolower(trim((string) $name));
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403);
        }
    }

    private function ensureHistoryTable(): void
    {
        if (Schema::hasTable('analytics_history')) {
            return;
        }

        Schema::create('analytics_history', function ($table) {
            $table->id();
            $table->date('service_date')->index();
            $table->unsignedTinyInteger('week_number')->nullable();
            $table->string('route_name');
            $table->string('route_key')->index();
            $table->string('account_name')->nullable();
            $table->string('client_type', 40)->default('commercial')->index();
            $table->decimal('gross_sales', 12, 2)->default(0);
            $table->decimal('hours', 8, 2)->default(0);
            $table->string('import_batch', 60)->nullable()->index();
            $table->timestamps();
        });
    }
}
