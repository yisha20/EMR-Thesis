@php
    $activeServing=$queueEntries->whereIn('status',['called','serving']);
    $patientName=function($entry){ return optional($entry->complaint)->student_name ?: optional(optional($entry->account)->user)->fullName(); };
@endphp
<section class="dashboard-panel queue-workspace" aria-labelledby="queue-operations-title">
    <div class="dashboard-panel-header">
        <div><p class="eyebrow">Queue operations</p><h2 id="queue-operations-title">Shared Clinic Queue</h2></div>
        @if(auth()->user()->role->name!=='Doctor')
        <form method="POST" action="{{ route('clinic-queues.policy') }}" class="queue-policy-form">@csrf @method('PATCH')
            <label for="queue-policy">Dispatch policy</label>
            <select id="queue-policy" name="policy" onchange="this.form.submit()">
                <option value="alternating" {{ $queuePolicy==='alternating'?'selected':'' }}>Alternating queues</option>
                <option value="strict_priority" {{ $queuePolicy==='strict_priority'?'selected':'' }}>Strict priority then oldest</option>
                <option value="manual" {{ $queuePolicy==='manual'?'selected':'' }}>Manual selection</option>
            </select>
        </form>
        @endif
    </div>
    <div class="queue-focus-grid">
        <article><h3>Now Serving</h3>
            @forelse($activeServing as $entry)<strong class="queue-ticket">{{ $entry->ticket_number }}</strong><span>{{ $patientName($entry) }} · {{ ucfirst($entry->status) }}</span>@empty<p>None</p>@endforelse
        </article>
        <article><h3>Next Patient</h3>
            @if($nextQueue)<strong class="queue-ticket">{{ $nextQueue->ticket_number }}</strong><span>{{ $patientName($nextQueue) }} · {{ ucfirst($nextQueue->queue_type) }} · {{ ucfirst($nextQueue->priority) }}</span>
                @if(auth()->user()->role->name!=='Doctor')<form method="POST" action="{{ route('clinic-queues.call-next') }}">@csrf<button class="btn btn-primary">Call Next</button></form>@endif
            @else<p>{{ $queuePolicy==='manual'?'Manual selection enabled.':'No patient waiting.' }}</p>@endif
        </article>
    </div>
    <div class="table-responsive-shell">
        <table class="table queue-table"><thead><tr><th>Queue</th><th>Patient</th><th>Type / ID</th><th>Complaint</th><th>Route</th><th>Priority</th><th>Waiting</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>@forelse($queueEntries as $entry)<tr>
            <td><strong>{{ $entry->ticket_number }}</strong></td><td>{{ $patientName($entry) }}</td>
            <td>{{ ucfirst(optional($entry->account)->patient_type ?: 'patient') }}<br><small>{{ optional($entry->complaint)->student_id_number }}</small></td>
            <td>{{ \Illuminate\Support\Str::limit(optional($entry->complaint)->chief_complaint,50) }}</td><td>{{ ucfirst($entry->queue_type) }}</td>
            <td><span class="urgency-badge urgency-{{ $entry->priority }}">{{ ucfirst($entry->priority) }}</span></td><td>{{ $entry->created_at->diffForHumans(null,true) }}</td><td>{{ ucfirst($entry->status) }}<br><span class="presence-badge presence-{{ $entry->presence_status }}" data-presence-badge="{{ $entry->id }}">{{ ucwords(str_replace('_',' ',$entry->presence_status)) }}</span></td>
            <td class="queue-actions">
            @if(auth()->user()->role->name==='Doctor')
                @if($entry->status==='called')<form method="POST" action="{{ route('student-complaints.start-consultation',$entry->complaint) }}">@csrf<button class="btn btn-sm btn-primary">Start Consultation</button></form>@endif
                <a class="btn btn-sm btn-light" href="{{ route('student-complaints.show',$entry->complaint) }}">View Patient Record</a>
            @else
                @foreach(['waiting'=>['called'=>'Call'],'called'=>['called'=>'Recall','serving'=>'Start Service','missed'=>'Mark Missed'],'serving'=>['completed'=>'Complete']] [$entry->status] ?? [] as $state=>$label)
                <form method="POST" action="{{ route('clinic-queues.update',$entry) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $state }}">@if($state==='missed')<input type="hidden" name="reason" value="Did not respond after recalls and grace period">@endif<button class="btn btn-sm {{ $state==='missed'?'btn-danger':'btn-primary' }}" data-confirm="{{ $state==='missed'?'Mark this patient as missed? The complaint record will remain.':$label.' '.$entry->ticket_number.'?' }}">{{ $label }}</button></form>
                @endforeach
                @if(in_array($entry->status,['waiting','called'],true))<form method="POST" action="{{ route('clinic-queues.transfer',$entry) }}">@csrf<input type="hidden" name="queue_type" value="{{ $entry->queue_type==='counter'?'consultation':'counter' }}"><input type="hidden" name="reason" value="Transferred by clinic staff"><button class="btn btn-sm btn-light" data-confirm="Transfer {{ $entry->ticket_number }} to the {{ $entry->queue_type==='counter'?'consultation':'counter' }} queue?">Transfer</button></form>@endif
                @if($entry->status==='missed')<form method="POST" action="{{ route('clinic-queues.requeue',$entry) }}">@csrf<button class="btn btn-sm btn-light" data-confirm="Return this missed patient to the end of the active queue?">Return to Queue</button></form>@endif
                @if(in_array($entry->status,['waiting','called'],true))<form method="POST" action="{{ route('clinic-queues.update',$entry) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="cancelled"><input type="hidden" name="reason" value="Cancelled from queue dashboard"><button class="btn btn-sm btn-outline-danger" data-confirm="Cancel {{ $entry->ticket_number }}?">Cancel</button></form>@endif
            @endif
            </td>
        </tr>@empty<tr><td colspan="9">No active queue entries today.</td></tr>@endforelse</tbody></table>
    </div>
</section>
@push('js')
<script>
(function(){var root=document.getElementById('queue-operations-title');if(!root||!window.fetch)return;var url='{{ route('clinic-queues.live') }}',known={{ $queueEntries->count() }};function poll(){if(document.hidden)return;fetch(url,{credentials:'same-origin',cache:'no-store',headers:{'Accept':'application/json'}}).then(function(r){return r.json()}).then(function(data){if(data.entries.length!==known){window.location.reload();return}data.entries.forEach(function(entry){var badge=document.querySelector('[data-presence-badge="'+entry.id+'"]');if(badge){badge.className='presence-badge presence-'+entry.presence_status;badge.textContent=entry.presence_label}})}).catch(function(){})}window.setInterval(poll,{{ config('clinic_queue.staff_poll_seconds',30)*1000 }});document.addEventListener('visibilitychange',function(){if(!document.hidden)poll()});})();
</script>
@endpush
