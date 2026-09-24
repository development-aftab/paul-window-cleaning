@extends('theme.layout.master')

@push('css')
    <style>
        .analytics_section .an_card { margin-bottom: 20px; }
        .analytics_section .an_card > h3 { font-size: 18px; margin: 0 0 14px; color: #32346A; }
        .analytics_section .an_sub { color: #6B7185; font-size: 13.5px; margin: -6px 0 14px; }

        /* ---------- Filters ---------- */
        .an_filter_grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; align-items: start; }
        .an_field label.an_label { display: block; font-weight: 600; font-size: 13.5px; color: #32346A; margin-bottom: 6px; }
        .an_field input[type="date"], .an_field select {
            width: 100%; min-height: 44px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px;
            background: #fff; color: #32346A; font-size: 15px;
        }
        .an_quick { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .an_chip {
            border: 1px solid #ddd; background: #fff; color: #32346A; border-radius: 50px;
            padding: 6px 14px; font-size: 13px; cursor: pointer; line-height: 1.2;
        }
        .an_chip:hover { border-color: #00ADEE; background: rgba(0, 173, 238, .06); }
        .an_weeks { display: flex; flex-wrap: wrap; gap: 8px; }
        .an_weeks label, .an_route_item {
            display: inline-flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;
        }
        .an_weeks label {
            border: 1px solid #ddd; border-radius: 8px; padding: 9px 14px; min-height: 44px; background: #fff; color: #32346A; font-size: 14px;
        }
        .an_weeks input:checked + span { font-weight: 700; color: #00ADEE; }
        .an_weeks label:has(input:checked) { border-color: #00ADEE; background: rgba(0, 173, 238, .07); }
        .an_routes_box { border: 1px solid #ddd; border-radius: 8px; padding: 6px 12px; max-height: 190px; overflow-y: auto; background: #fff; }
        .an_route_item { width: 100%; padding: 8px 0; border-bottom: 1px solid #f0f2f6; font-size: 14px; color: #32346A; }
        .an_route_item:last-child { border-bottom: 0; }
        .an_route_group { font-size: 11.5px; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; padding: 8px 0 2px; }
        .an_actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; align-items: center; }
        .an_check { display: inline-flex; align-items: center; gap: 8px; font-size: 14px; color: #32346A; cursor: pointer; }

        /* ---------- KPI tiles ---------- */
        .an_kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 20px; }
        .an_kpi { background: #fff; border-radius: 12px; padding: 16px; border: 1px solid #E7EBF0; box-shadow: 0 2px 10px rgba(0,0,0,.03); }
        .an_kpi span { display: block; font-size: 12.5px; text-transform: uppercase; letter-spacing: .4px; color: #6B7185; }
        .an_kpi strong { display: block; font-size: 24px; color: #32346A; margin-top: 4px; word-break: break-word; }

        /* ---------- Chart ---------- */
        .an_chart_controls { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-bottom: 14px; }
        .an_seg { display: inline-flex; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .an_seg button { border: 0; background: #fff; padding: 10px 16px; font-size: 14px; color: #32346A; cursor: pointer; min-height: 42px; }
        .an_seg button + button { border-left: 1px solid #ddd; }
        .an_seg button.active { background: #00ADEE; color: #fff; }
        .an_chart_controls select { min-height: 42px; border: 1px solid #ddd; border-radius: 8px; padding: 6px 12px; background: #fff; color: #32346A; font-size: 14px; }
        .an_chart_wrap { position: relative; height: 380px; }
        .an_empty { text-align: center; color: #6B7185; padding: 40px 10px; }

        /* ---------- Tables ---------- */
        .an_table_wrap { max-height: 460px; overflow: auto; border: 1px solid #eef0f5; border-radius: 10px; }
        .an_table { width: 100%; margin: 0; min-width: 560px; }
        .an_table th { position: sticky; top: 0; background: #F4F9FB; z-index: 1; white-space: nowrap; cursor: pointer; font-size: 13px; color: #32346A; }
        .an_table th, .an_table td { padding: 10px 12px; font-size: 14px; }
        .an_table td.num, .an_table th.num { text-align: right; white-space: nowrap; }
        .an_table tfoot td { font-weight: 700; background: #F4F9FB; }
        .an_table tbody tr:nth-child(even) { background: #fafbfd; }
        .an_toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .an_toolbar input[type="search"] { min-height: 42px; border: 1px solid #ddd; border-radius: 8px; padding: 6px 12px; min-width: 220px; }
        .an_notes { font-size: 12.5px; color: #6B7185; margin-top: 12px; }
        .an_notes p { margin: 0 0 4px; }
        .an_loading { opacity: .5; pointer-events: none; }
        .an_alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px; }
        .an_alert.success { background: #dcfce7; color: #166534; }
        .an_alert.error { background: #fee2e2; color: #991b1b; }
        .an_import_grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
        .an_import_grid ul { padding-left: 18px; margin: 0; font-size: 13.5px; color: #32346A; }

        @media (max-width: 991.98px) {
            .an_kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .an_chart_wrap { height: 340px; }
        }
        @media (max-width: 575.98px) {
            .an_kpi strong { font-size: 20px; }
            .an_chart_wrap { height: 300px; }
            .an_actions .btn_global, .an_toolbar .btn_global { flex: 1 1 100%; justify-content: center; }
            .an_toolbar input[type="search"] { width: 100%; min-width: 0; }
            .an_seg { width: 100%; }
            .an_seg button { flex: 1; padding-left: 8px; padding-right: 8px; }
            .an_chart_controls select { flex: 1 1 100%; }
        }
    </style>
@endpush

@section('navbar-title')
    <div class="custom_justify_between">
        <h2 class="navbar_PageTitle">Analytics</h2>
    </div>
@endsection

@section('content')
    <section class="analytics_section">
        <div class="container-fluid custom_container">

            @if (session('analytics_status'))
                <div class="an_alert {{ session('analytics_status')['type'] }}">{{ session('analytics_status')['text'] }}</div>
            @endif
            @if ($errors->any())
                <div class="an_alert error">{{ $errors->first() }}</div>
            @endif

            {{-- ============================ FILTERS ============================ --}}
            <div class="custom_div an_card">
                <h3>Gross Commercial Sales</h3>
                <p class="an_sub">Choose what to look at, then press Apply. Leave weeks or routes empty to include all of them.</p>

                <form id="an_filters" onsubmit="return false;" autocomplete="off">
                    <div class="an_filter_grid">
                        <div class="an_field">
                            <label class="an_label" for="an_from">From</label>
                            <input type="date" id="an_from" value="{{ $defaultFrom }}">
                            <label class="an_label" for="an_to" style="margin-top:12px;">To</label>
                            <input type="date" id="an_to" value="{{ $defaultTo }}">
                            <div class="an_quick">
                                <button type="button" class="an_chip" data-range="this_year">This year</button>
                                <button type="button" class="an_chip" data-range="last_year">Last year</button>
                                <button type="button" class="an_chip" data-range="12m">Last 12 months</button>
                                <button type="button" class="an_chip" data-range="5y">Last 5 years</button>
                                <button type="button" class="an_chip" data-range="all">All time</button>
                            </div>
                        </div>

                        <div class="an_field">
                            <span class="an_label">Weeks (of the 4-week cycle)</span>
                            <div class="an_weeks">
                                @foreach ([1, 2, 3, 4] as $w)
                                    <label><input type="checkbox" class="an_week" value="{{ $w }}"><span>Week {{ $w }}</span></label>
                                @endforeach
                            </div>
                            <div class="an_field" style="margin-top:16px;">
                                <label class="an_label" for="an_group">Break down by</label>
                                <select id="an_group">
                                    <option value="route">Route</option>
                                    <option value="week">Week of cycle (1-4)</option>
                                    <option value="route_week">Route + week</option>
                                    <option value="calendar_week">Calendar week (by date)</option>
                                    <option value="cycle">Cycle (4-week period)</option>
                                    <option value="year">Year</option>
                                </select>
                            </div>
                        </div>

                        <div class="an_field">
                            <span class="an_label">Routes</span>
                            <div class="an_routes_box">
                                <label class="an_route_item"><input type="checkbox" id="an_all_routes" checked> <strong>All routes</strong></label>
                                @foreach ($routes as $route)
                                    <label class="an_route_item"><input type="checkbox" class="an_route" value="{{ $route->id }}"> {{ $route->name }}</label>
                                @endforeach
                                @if ($historyRoutes->count())
                                    <div class="an_route_group">Imported history only</div>
                                    @foreach ($historyRoutes as $hr)
                                        <label class="an_route_item"><input type="checkbox" class="an_route" value="h:{{ $hr->route_name }}"> {{ $hr->route_name }}</label>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="an_field">
                            <label class="an_label" for="an_basis">Hours based on</label>
                            <select id="an_basis">
                                <option value="logged">Logged route hours (matches Route Report)</option>
                                <option value="jobs">Job start/end times (commercial jobs only)</option>
                            </select>
                            <label class="an_check" style="margin-top:14px;">
                                <input type="checkbox" id="an_history" checked> Include imported history
                            </label>
                        </div>
                    </div>

                    <div class="an_actions">
                        <button type="button" class="btn_global btn_blue" id="an_apply">Apply <i class="fa-solid fa-check"></i></button>
                        <button type="button" class="btn_global btn_grey" id="an_reset">Reset</button>
                    </div>
                </form>
            </div>

            {{-- ============================== RESULTS ============================== --}}
            <div id="an_results">
                <div class="an_kpis">
                    <div class="an_kpi"><span>Gross sales</span><strong id="kpi_sales">-</strong></div>
                    <div class="an_kpi"><span>Hours</span><strong id="kpi_hours">-</strong></div>
                    <div class="an_kpi"><span>Sales per hour</span><strong id="kpi_ph">-</strong></div>
                    <div class="an_kpi"><span>Jobs</span><strong id="kpi_jobs">-</strong></div>
                </div>

                <div class="custom_div an_card">
                    <h3>Chart</h3>
                    <div class="an_chart_controls">
                        <div class="an_seg" id="an_chart_type">
                            <button type="button" data-type="bar" class="active">Bar</button>
                            <button type="button" data-type="horizontal">Horizontal bar</button>
                            <button type="button" data-type="pie">Pie</button>
                        </div>
                        <select id="an_metric">
                            <option value="sales">Gross sales ($)</option>
                            <option value="hours">Hours</option>
                            <option value="per_hour">Sales per hour ($/hr)</option>
                        </select>
                    </div>
                    <div class="an_chart_wrap"><canvas id="an_chart"></canvas></div>
                    <div class="an_empty" id="an_chart_empty" style="display:none;">No data for these filters.</div>
                </div>

                <div class="custom_div an_card">
                    <div class="an_toolbar">
                        <h3 style="margin:0;">Breakdown</h3>
                        <button type="button" class="btn_global btn_dark_blue" id="an_export">Export CSV <i class="fa-solid fa-file-csv"></i></button>
                    </div>
                    <div class="table-responsive an_table_wrap">
                        <table class="table an_table" id="an_summary_table">
                            <thead>
                                <tr>
                                    <th data-sort="label">Name</th>
                                    <th class="num" data-sort="sales">Gross sales</th>
                                    <th class="num" data-sort="hours">Hours</th>
                                    <th class="num" data-sort="per_hour">$ / hour</th>
                                    <th class="num" data-sort="jobs">Jobs</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                    <div class="an_notes" id="an_notes"></div>
                </div>

                <div class="custom_div an_card">
                    <div class="an_toolbar">
                        <h3 style="margin:0;">Hours per account</h3>
                        <input type="search" id="an_account_search" placeholder="Search accounts or routes">
                    </div>
                    <p class="an_sub">How long each account takes to service. Uses the start/end time recorded on completed jobs plus any hours in imported history. Visits without a recorded time are counted but not averaged.</p>
                    <div class="table-responsive an_table_wrap">
                        <table class="table an_table" id="an_accounts_table" style="min-width:680px;">
                            <thead>
                                <tr>
                                    <th data-sort="account">Account</th>
                                    <th data-sort="route">Route</th>
                                    <th class="num" data-sort="visits">Visits</th>
                                    <th class="num" data-sort="hours">Total hrs</th>
                                    <th class="num" data-sort="avg_hours">Avg hrs / visit</th>
                                    <th class="num" data-sort="sales">Gross sales</th>
                                    <th class="num" data-sort="per_hour">$ / hour</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if(false)
            {{-- ============================== IMPORT ============================== --}}
            <div class="custom_div an_card">
                <h3>Import past data</h3>
                <p class="an_sub">Load historical sales (for example the past 5 years) from a CSV file. Excel users: File &gt; Save As &gt; CSV.</p>
                <div class="an_import_grid">
                    <div>
                        <form method="POST" action="{{ route('analytics.import') }}" enctype="multipart/form-data">
                            @csrf
                            <label class="an_label" for="an_file" style="font-weight:600;color:#32346A;">CSV file</label>
                            <input type="file" id="an_file" name="file" accept=".csv,.txt" required class="form-control" style="margin:6px 0 12px;">
                            <div class="an_actions" style="margin-top:0;">
                                <button type="submit" class="btn_global btn_blue">Import <i class="fa-solid fa-upload"></i></button>
                                <a href="{{ route('analytics.template') }}" class="btn_global btn_grey" style="text-decoration:none;">Download template</a>
                            </div>
                        </form>
                    </div>
                    <div>
                        <strong style="color:#32346A;">Columns</strong>
                        <ul>
                            <li><b>date</b> (required) - 2021-03-08 or 3/8/2021</li>
                            <li><b>route</b> (required) - route name</li>
                            <li><b>sales</b> (required) - gross sales, e.g. 450.00</li>
                            <li>account - the commercial account (enables the hours-per-account view)</li>
                            <li>hours - hours worked for that line</li>
                            <li>week - 1 to 4 (worked out from the date if blank)</li>
                            <li>client_type - defaults to commercial</li>
                        </ul>
                    </div>
                </div>

                @if ($batches->count())
                    <h3 style="margin-top:24px;">Imported batches</h3>
                    <div class="table-responsive an_table_wrap" style="max-height:260px;">
                        <table class="table an_table" style="min-width:520px;">
                            <thead>
                                <tr><th>Batch</th><th class="num">Rows</th><th>Date range</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach ($batches as $b)
                                    <tr>
                                        <td>{{ $b->import_batch ?? 'n/a' }}</td>
                                        <td class="num">{{ number_format($b->rows_count) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($b->first_date)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($b->last_date)->format('M j, Y') }}</td>
                                        <td class="num">
                                            @if ($b->import_batch)
                                                <form method="POST" action="{{ route('analytics.history.delete') }}" class="delete-form" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="batch" value="{{ $b->import_batch }}">
                                                    <i class="fa-solid fa-trash text-danger" style="cursor:pointer;padding:6px;" title="Remove this import" onclick="showDeleteConfirmation(this)"></i>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @endif

        </div>
    </section>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            var DATA_URL = {!! json_encode(route('analytics.data')) !!};
            var DEFAULT_FROM = {!! json_encode($defaultFrom) !!};
            var DEFAULT_TO = {!! json_encode($defaultTo) !!};
            var PALETTE = ['#00ADEE', '#32346A', '#F59E0B', '#10B981', '#EF4444', '#8B5CF6', '#14B8A6', '#F97316', '#3B82F6', '#EC4899', '#84CC16', '#64748B'];

            var state = { summary: null, accounts: [], chart: null, type: 'bar', metric: 'sales', sort: { key: 'sales', dir: -1 }, accSort: { key: 'hours', dir: -1 } };

            var $ = function (id) { return document.getElementById(id); };
            var money = function (n) { return n == null ? '-' : Number(n).toLocaleString('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }); };
            var num = function (n) { return n == null ? '-' : Number(n).toLocaleString('en-US', { maximumFractionDigits: 2 }); };
            var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
            var pad = function (n) { return (n < 10 ? '0' : '') + n; };
            var iso = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };

            function query() {
                var p = new URLSearchParams();
                p.set('from', $('an_from').value);
                p.set('to', $('an_to').value);
                p.set('group_by', $('an_group').value);
                p.set('hours_basis', $('an_basis').value);
                p.set('include_history', $('an_history').checked ? '1' : '0');
                document.querySelectorAll('.an_week:checked').forEach(function (c) { p.append('weeks[]', c.value); });
                if (!$('an_all_routes').checked) {
                    document.querySelectorAll('.an_route:checked').forEach(function (c) { p.append('routes[]', c.value); });
                }
                return p.toString();
            }

            function load() {
                var box = $('an_results');
                box.classList.add('an_loading');
                fetch(DATA_URL + '?' + query(), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { if (!r.ok) { throw new Error('HTTP ' + r.status); } return r.json(); })
                    .then(function (d) {
                        state.summary = d.summary;
                        state.accounts = d.accounts || [];
                        renderKpis(d.summary.totals);
                        renderSummary();
                        renderChart();
                        renderAccounts();
                        $('an_notes').innerHTML = (d.notes || []).map(function (n) { return '<p>' + esc(n) + '</p>'; }).join('');
                    })
                    .catch(function (e) {
                        $('an_notes').innerHTML = '<p style="color:#b91c1c;">Could not load analytics (' + esc(e.message) + '). Please try again.</p>';
                    })
                    .then(function () { box.classList.remove('an_loading'); });
            }

            function renderKpis(t) {
                $('kpi_sales').textContent = money(t.sales);
                $('kpi_hours').textContent = num(t.hours);
                $('kpi_ph').textContent = t.per_hour == null ? '-' : money(t.per_hour);
                $('kpi_jobs').textContent = num(t.jobs);
            }

            function sorted(rows, s) {
                return rows.slice().sort(function (a, b) {
                    var x = a[s.key], y = b[s.key];
                    if (x == null) { x = -Infinity; } if (y == null) { y = -Infinity; }
                    if (typeof x === 'string') { return x.localeCompare(String(y)) * s.dir; }
                    return (x - y) * s.dir;
                });
            }

            function renderSummary() {
                var rows = sorted(state.summary.rows, state.sort), t = state.summary.totals;
                $('an_summary_table').querySelector('tbody').innerHTML = rows.length ? rows.map(function (r) {
                    return '<tr><td>' + esc(r.label) + '</td><td class="num">' + money(r.sales) + '</td><td class="num">' + num(r.hours) +
                        '</td><td class="num">' + (r.per_hour == null ? '-' : money(r.per_hour)) + '</td><td class="num">' + num(r.jobs) + '</td></tr>';
                }).join('') : '<tr><td colspan="5" class="an_empty">No data for these filters.</td></tr>';
                $('an_summary_table').querySelector('tfoot').innerHTML = rows.length ?
                    '<tr><td>Total</td><td class="num">' + money(t.sales) + '</td><td class="num">' + num(t.hours) + '</td><td class="num">' +
                    (t.per_hour == null ? '-' : money(t.per_hour)) + '</td><td class="num">' + num(t.jobs) + '</td></tr>' : '';
            }

            function renderAccounts() {
                var q = ($('an_account_search').value || '').toLowerCase();
                var rows = state.accounts.filter(function (a) {
                    return !q || (a.account || '').toLowerCase().indexOf(q) > -1 || (a.route || '').toLowerCase().indexOf(q) > -1;
                });
                rows = sorted(rows, state.accSort).slice(0, 500);
                $('an_accounts_table').querySelector('tbody').innerHTML = rows.length ? rows.map(function (a) {
                    return '<tr><td>' + esc(a.account) + '</td><td>' + esc(a.route) + '</td><td class="num">' + num(a.visits) + '</td><td class="num">' +
                        num(a.hours) + '</td><td class="num">' + num(a.avg_hours) + '</td><td class="num">' + money(a.sales) + '</td><td class="num">' +
                        (a.per_hour == null ? '-' : money(a.per_hour)) + '</td></tr>';
                }).join('') : '<tr><td colspan="7" class="an_empty">No accounts for these filters.</td></tr>';
            }

            function renderChart() {
                var rows = state.summary.rows.slice(), metric = state.metric, type = state.type;
                var empty = !rows.length;
                $('an_chart_empty').style.display = empty ? 'block' : 'none';
                $('an_chart').parentNode.style.display = empty ? 'none' : 'block';
                if (state.chart) { state.chart.destroy(); state.chart = null; }
                if (empty) { return; }

                var labels, values;
                if (type === 'pie' && metric !== 'per_hour' && rows.length > 10) {
                    rows.sort(function (a, b) { return (b[metric] || 0) - (a[metric] || 0); });
                    var top = rows.slice(0, 10), rest = rows.slice(10).reduce(function (s, r) { return s + (r[metric] || 0); }, 0);
                    labels = top.map(function (r) { return r.label; }).concat(['Other']);
                    values = top.map(function (r) { return r[metric] || 0; }).concat([Math.round(rest * 100) / 100]);
                } else {
                    labels = rows.map(function (r) { return r.label; });
                    values = rows.map(function (r) { return r[metric] == null ? 0 : r[metric]; });
                }

                var title = { sales: 'Gross sales', hours: 'Hours', per_hour: 'Sales per hour' }[metric];
                var fmt = function (v) { return metric === 'hours' ? num(v) + ' hrs' : money(v); };
                var isPie = type === 'pie';

                state.chart = new Chart($('an_chart').getContext('2d'), {
                    type: isPie ? 'pie' : 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: title, data: values,
                            backgroundColor: isPie ? labels.map(function (l, i) { return PALETTE[i % PALETTE.length]; }) : '#00ADEE',
                            borderRadius: isPie ? 0 : 6, borderWidth: isPie ? 2 : 0, borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        indexAxis: type === 'horizontal' ? 'y' : 'x',
                        plugins: {
                            legend: { display: isPie, position: 'bottom' },
                            tooltip: { callbacks: { label: function (c) { return ' ' + c.label + ': ' + fmt(isPie ? c.parsed : (type === 'horizontal' ? c.parsed.x : c.parsed.y)); } } }
                        },
                        scales: isPie ? {} : (type === 'horizontal'
                            ? { x: { beginAtZero: true, ticks: { callback: function (v) { return metric === 'hours' ? v : '$' + v; } } } }
                            : { y: { beginAtZero: true, ticks: { callback: function (v) { return metric === 'hours' ? v : '$' + v; } } }, x: { ticks: { maxRotation: 60, autoSkip: false } } })
                    }
                });
            }

            function exportCsv() {
                if (!state.summary) { return; }
                var lines = [['Name', 'Gross sales', 'Hours', 'Sales per hour', 'Jobs']];
                state.summary.rows.forEach(function (r) { lines.push([r.label, r.sales, r.hours, r.per_hour == null ? '' : r.per_hour, r.jobs]); });
                var t = state.summary.totals;
                lines.push(['Total', t.sales, t.hours, t.per_hour == null ? '' : t.per_hour, t.jobs]);
                var csv = lines.map(function (l) { return l.map(function (c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(','); }).join('\r\n');
                var a = document.createElement('a');
                a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
                a.download = 'analytics_' + $('an_from').value + '_to_' + $('an_to').value + '.csv';
                document.body.appendChild(a); a.click(); document.body.removeChild(a);
            }

            function setRange(kind) {
                var now = new Date(), y = now.getFullYear(), from, to = now;
                if (kind === 'this_year') { from = new Date(y, 0, 1); }
                else if (kind === 'last_year') { from = new Date(y - 1, 0, 1); to = new Date(y - 1, 11, 31); }
                else if (kind === '12m') { from = new Date(y - 1, now.getMonth(), now.getDate() + 1); }
                else if (kind === '5y') { from = new Date(y - 5, now.getMonth(), now.getDate() + 1); }
                else { from = new Date(2000, 0, 1); }
                $('an_from').value = iso(from);
                $('an_to').value = iso(to);
                load();
            }

            // ---- wiring ----
            $('an_apply').addEventListener('click', load);
            $('an_reset').addEventListener('click', function () {
                $('an_from').value = DEFAULT_FROM; $('an_to').value = DEFAULT_TO;
                $('an_group').value = 'route'; $('an_basis').value = 'logged'; $('an_history').checked = true;
                document.querySelectorAll('.an_week, .an_route').forEach(function (c) { c.checked = false; });
                $('an_all_routes').checked = true;
                load();
            });
            document.querySelectorAll('.an_chip').forEach(function (b) { b.addEventListener('click', function () { setRange(b.getAttribute('data-range')); }); });

            $('an_all_routes').addEventListener('change', function () {
                if (this.checked) { document.querySelectorAll('.an_route').forEach(function (c) { c.checked = false; }); }
            });
            document.querySelectorAll('.an_route').forEach(function (c) {
                c.addEventListener('change', function () {
                    var any = document.querySelectorAll('.an_route:checked').length > 0;
                    $('an_all_routes').checked = !any;
                });
            });

            document.querySelectorAll('#an_chart_type button').forEach(function (b) {
                b.addEventListener('click', function () {
                    document.querySelectorAll('#an_chart_type button').forEach(function (x) { x.classList.remove('active'); });
                    b.classList.add('active'); state.type = b.getAttribute('data-type');
                    if (state.summary) { renderChart(); }
                });
            });
            $('an_metric').addEventListener('change', function () { state.metric = this.value; if (state.summary) { renderChart(); } });
            $('an_export').addEventListener('click', exportCsv);
            $('an_account_search').addEventListener('input', function () { if (state.summary) { renderAccounts(); } });

            document.querySelectorAll('#an_summary_table th[data-sort]').forEach(function (th) {
                th.addEventListener('click', function () {
                    var k = th.getAttribute('data-sort');
                    state.sort = { key: k, dir: state.sort.key === k ? -state.sort.dir : (k === 'label' ? 1 : -1) };
                    if (state.summary) { renderSummary(); }
                });
            });
            document.querySelectorAll('#an_accounts_table th[data-sort]').forEach(function (th) {
                th.addEventListener('click', function () {
                    var k = th.getAttribute('data-sort');
                    state.accSort = { key: k, dir: state.accSort.key === k ? -state.accSort.dir : (k === 'account' || k === 'route' ? 1 : -1) };
                    if (state.summary) { renderAccounts(); }
                });
            });

            load();
        })();
    </script>
@endpush
