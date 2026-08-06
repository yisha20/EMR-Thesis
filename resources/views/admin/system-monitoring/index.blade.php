@extends('layouts.app')

@push('css')
<style>
.monitor-page{max-width:1500px;margin:0 auto;color:#123b56}.monitor-head{display:flex;justify-content:space-between;gap:24px;align-items:flex-start;margin:8px 0 22px}.monitor-head .eyebrow{margin:0;color:#398fbd;font-size:12px;font-weight:800;letter-spacing:.08em}.monitor-head h1{font-size:30px;font-weight:800;margin:3px 0}.monitor-head p{margin:0;color:#66859a}.monitor-actions{display:flex;flex-wrap:wrap;gap:9px}.monitor-btn{border:1px solid #acd3ea;background:#fff;color:#164c6d;padding:10px 14px;border-radius:9px;font-weight:700;white-space:nowrap}.monitor-btn.primary{background:#2e95c5;color:white;border-color:#2e95c5}.monitor-meta{background:#eaf5fb;border:1px solid #bcdcee;border-radius:12px;padding:13px 16px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:18px}.monitor-meta small,.monitor-card small{display:block;text-transform:uppercase;font-size:10px;font-weight:800;color:#61839a}.monitor-meta strong{display:block;margin-top:3px}.monitor-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:24px}.monitor-card{background:#fff;border:1px solid #c7deec;border-radius:13px;padding:16px;min-width:0;box-shadow:0 2px 7px rgba(23,73,101,.05)}.monitor-card-top{display:flex;justify-content:space-between;gap:8px;align-items:flex-start}.monitor-card h3{font-size:15px;font-weight:800;margin:0}.monitor-count{font-size:28px;font-weight:800;margin:8px 0 2px}.monitor-card p{font-size:12px;color:#66859a;margin:0;line-height:1.45}.status-pill{padding:4px 8px;border-radius:99px;font-size:10px;font-weight:800;text-transform:uppercase}.status-healthy{background:#def7e8;color:#087147}.status-warning{background:#fff2ce;color:#8a5a00}.status-critical{background:#ffe1e4;color:#a42e3b}.status-not_configured{background:#e7edf1;color:#58707e}.monitor-section{background:#fff;border:1px solid #c7deec;border-radius:13px;margin-bottom:20px;overflow:hidden}.monitor-section-head{padding:17px 18px;border-bottom:1px solid #d9e8f1;display:flex;justify-content:space-between;align-items:center}.monitor-section-head h2{font-size:18px;font-weight:800;margin:0}.monitor-table-wrap{overflow-x:auto}.monitor-table{width:100%;min-width:900px;border-collapse:collapse}.monitor-table th{background:#edf5f9;color:#56778c;text-transform:uppercase;font-size:10px;letter-spacing:.04em}.monitor-table th,.monitor-table td{padding:12px 14px;border-bottom:1px solid #e1edf3;text-align:left;vertical-align:middle}.severity{font-size:10px;font-weight:800;text-transform:uppercase}.severity-critical{color:#a32235}.severity-high{color:#bd5720}.severity-medium{color:#9a6900}.incident-actions{display:flex;gap:6px}.incident-actions form{margin:0}.incident-actions button,.incident-actions a{font-size:11px;padding:6px 8px;border-radius:6px;border:1px solid #b7d6e8;background:white;color:#145170;font-weight:700}.monitor-alert{padding:13px 16px;border-radius:9px;margin-bottom:16px}.monitor-alert.success{background:#def7e8;color:#08633f}.monitor-alert.error{background:#ffe3e6;color:#982b38}.monitor-empty{padding:28px;text-align:center;color:#66859a}.dev-note{padding:16px;background:#f5f9fb;border:1px dashed #b6d4e5;border-radius:12px;margin-bottom:20px}.dev-note h2{font-size:16px;font-weight:800;margin:0 0 5px}
@media(max-width:1024px){.monitor-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.monitor-meta{grid-template-columns:repeat(2,minmax(0,1fr))}.monitor-head{flex-direction:column}.monitor-actions{width:100%}}
@media(max-width:575px){.monitor-page{padding:0 2px}.monitor-head h1{font-size:25px}.monitor-actions{display:grid;grid-template-columns:1fr 1fr}.monitor-btn{padding:9px 8px;text-align:center;font-size:12px}.monitor-actions form:first-child{grid-column:1/-1}.monitor-actions form .monitor-btn{width:100%}.monitor-grid{grid-template-columns:1fr}.monitor-meta{grid-template-columns:1fr 1fr;gap:11px}.monitor-card{padding:14px}.monitor-section-head{padding:14px}}
</style>
@endpush

@section('content')
<div class="monitor-page">
    <header class="monitor-head">
        <div><p class="eyebrow">SYSTEM ADMINISTRATION</p><h1>System Monitoring</h1><p>Review application health, workflow integrity, and pilot incidents.</p></div>
        <div class="monitor-actions">
            <form method="POST" action="{{ route('admin.monitoring.run') }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').innerText='Running checks...';">@csrf<button class="monitor-btn primary" type="submit"><i class="fa fa-play-circle"></i> Run Checks Now</button></form>
            <a class="monitor-btn" href="{{ route('admin.monitoring.index') }}"><i class="fa fa-refresh"></i> Refresh</a>
            <a class="monitor-btn" href="{{ route('admin.monitoring.reports.daily') }}"><i class="fa fa-download"></i> Export Daily Report</a>
        </div>
    </header>
    @if(session('success'))<div class="monitor-alert success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="monitor-alert error">{{ session('error') }}</div>@endif
    <section class="monitor-meta">
        <div><small>Environment</small><strong>{{ app()->environment('local') ? 'Local Development' : ucfirst(app()->environment()) }}</strong></div>
        <div><small>Application Version</small><strong>{{ config('app.version', 'Pilot Build') }}</strong></div>
        <div><small>Current Time</small><strong>{{ now()->format('M j, Y g:i A') }}</strong></div>
        <div><small>Last Check</small><strong>{{ $result['checked_at']->format('M j, Y g:i A') }}</strong></div>
    </section>
    @if(app()->environment('local'))<section class="dev-note"><h2><i class="fa fa-code"></i> Local Development</h2><p class="mb-0">Manual checks are available. Scheduler and local backup verification are not treated as production failures.</p></section>@endif

    <section class="monitor-grid" aria-label="System health">
        @foreach(collect($result['checks'])->take(8) as $check)
        <article class="monitor-card"><div class="monitor-card-top"><h3>{{ $check['label'] }}</h3><span class="status-pill status-{{ $check['status'] }}">{{ str_replace('_',' ',$check['status']) }}</span></div><p style="margin-top:10px">{{ $check['message'] }}</p></article>
        @endforeach
    </section>

    <div class="monitor-section-head" style="padding-left:0;border:0"><h2>Workflow Integrity</h2></div>
    <section class="monitor-grid">
        @foreach(collect($result['checks'])->skip(8) as $check)
        <article class="monitor-card"><div class="monitor-card-top"><h3>{{ $check['label'] }}</h3><span class="status-pill status-{{ $check['status'] }}">{{ $check['severity'] }}</span></div><div class="monitor-count">{{ $check['count'] }}</div><p>{{ $check['message'] }}</p></article>
        @endforeach
    </section>

    <section class="monitor-section">
        <div class="monitor-section-head"><h2>Recent Incidents</h2><small>Patient clinical content is excluded</small></div>
        @if($incidents->count())<div class="monitor-table-wrap"><table class="monitor-table"><thead><tr><th>Reference</th><th>Detected</th><th>Severity</th><th>Category</th><th>Event</th><th>Safe Resource</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @foreach($incidents as $incident)<tr><td><strong>{{ $incident->reference_code }}</strong></td><td>{{ $incident->detected_at->format('M j, g:i A') }}</td><td><span class="severity severity-{{ $incident->severity }}">{{ $incident->severity }}</span></td><td>{{ ucfirst($incident->category) }}</td><td>{{ str_replace('_',' ',ucfirst($incident->event_type)) }}</td><td>{{ $incident->resource_type ? ucfirst($incident->resource_type).' #'.$incident->resource_id : 'System' }}</td><td>{{ str_replace('_',' ',ucfirst($incident->status)) }}</td><td><div class="incident-actions"><a href="{{ route('admin.monitoring.incidents.show',$incident) }}">Details</a>@if($incident->status==='open')<form method="POST" action="{{ route('admin.monitoring.incidents.status',$incident) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="investigating"><button>Investigate</button></form>@endif</div></td></tr>@endforeach
        </tbody></table></div><div class="p-3">{{ $incidents->links() }}</div>@else<div class="monitor-empty"><i class="fa fa-check-circle fa-2x"></i><p>No monitoring incidents recorded.</p></div>@endif
    </section>
</div>
@endsection
