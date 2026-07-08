<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $prescription->prescription_number }} - MSU-IIT Clinic</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { background:#eef6fa; color:#123b56; font-family:Arial,sans-serif; margin:0; padding:24px; }
        .prescription-toolbar { display:flex; gap:10px; justify-content:center; margin:0 auto 18px; max-width:820px; }
        #printSection { display:block !important; }
        .prescription-document { background:#fff; border:1px solid #b7d9ee; box-shadow:0 16px 42px rgba(20,75,110,.12); box-sizing:border-box; margin:auto; max-width:820px; min-height:1050px; padding:42px 52px; }
        body.paper-letter .prescription-document { max-width:816px; min-height:1056px; }
        body.layout-compact .prescription-document { min-height:0; padding:30px 42px; }
        .prescription-header { align-items:center; border-bottom:2px solid #2f78a0; display:flex; gap:18px; padding-bottom:18px; text-align:center; }
        .prescription-logo { align-items:center; background:linear-gradient(135deg,#2f78a0,#58a6cf); border:6px solid #d9eef8; border-radius:50%; color:#fff; display:flex; flex-direction:column; height:76px; justify-content:center; line-height:1; overflow:hidden; width:76px; }
        .prescription-logo strong { font-size:14px; letter-spacing:0; }
        .prescription-logo span { background:#fff; border-radius:50%; color:#2f78a0; font-size:18px; font-weight:bold; height:26px; line-height:24px; margin-top:3px; text-align:center; width:26px; }
        .prescription-header div { flex:1; }
        .prescription-header h1 { font-size:24px; margin:5px 0; }
        .prescription-header span,.prescription-meta span { color:#627f91; font-size:12px; }
        .prescription-meta { border-bottom:1px solid #b7d9ee; display:grid; gap:14px 24px; grid-template-columns:repeat(2,1fr); padding:22px 0; }
        .prescription-meta div { display:flex; flex-direction:column; }
        .prescription-rx { min-height:310px; padding:26px 0; }
        .rx-symbol { color:#2f78a0; font-family:Georgia,serif; font-size:46px; font-style:italic; font-weight:bold; }
        .prescription-type { color:#627f91; font-size:12px; font-weight:bold; text-transform:uppercase; }
        .prescribed-medication { border:1px solid #d9e8f0; border-radius:8px; margin:12px 0; padding:14px; }
        .prescribed-medication h2 { font-size:18px; margin:0 0 6px; }
        .prescribed-medication dl { display:grid; gap:10px 16px; grid-template-columns:repeat(2,1fr); margin:0; }
        .prescribed-medication div { min-width:0; }
        .prescribed-medication dt { color:#627f91; font-size:11px; font-weight:bold; text-transform:uppercase; }
        .prescribed-medication dd { margin:3px 0 0; }
        .prescribed-medication p,.prescription-instructions p { line-height:1.6; margin:0; }
        .prescription-instructions { border-top:1px solid #b7d9ee; padding-top:18px; }
        .prescription-instructions h2 { font-size:14px; text-transform:uppercase; }
        .prescription-follow-up { margin-top:20px; }
        .prescription-signature { display:flex; flex-direction:column; margin-left:auto; margin-top:80px; text-align:center; width:280px; }
        .signature-line { border-top:1px solid #123b56; margin-bottom:8px; }
        .prescription-signature span { color:#627f91; font-size:12px; }
        body.layout-compact .prescription-rx { min-height:0; padding:18px 0; }
        body.layout-compact .prescribed-medication { margin:8px 0; padding:10px 12px; }
        body.hide-clinic-header [data-print-section="clinic-header"],
        body.hide-signature [data-print-section="signature"],
        body.hide-follow-up [data-print-section="follow-up"],
        body.hide-additional-instructions [data-print-section="additional-instructions"] { display:none !important; }
        .print-settings-overlay { align-items:center; background:rgba(18,59,86,.34); display:none; inset:0; justify-content:center; padding:18px; position:fixed; z-index:1000; }
        .print-settings-overlay.is-visible { display:flex; }
        .print-settings-panel { background:#fff; border:1px solid #b7d9ee; border-radius:10px; box-shadow:0 24px 60px rgba(20,75,110,.24); max-width:520px; padding:20px; width:100%; }
        .print-settings-panel h2 { color:#123b56; font-size:20px; margin:0 0 14px; }
        .print-settings-grid { display:grid; gap:10px; }
        .print-settings-row { align-items:center; display:flex; gap:10px; justify-content:space-between; }
        .print-settings-row label { color:#123b56; font-weight:700; margin:0; }
        .print-settings-row select { border:1px solid #b7d9ee; border-radius:8px; min-height:38px; padding:6px 10px; }
        .print-settings-check { align-items:center; display:flex; gap:9px; }
        .print-settings-actions { border-top:1px solid #d9e8f0; display:flex; gap:10px; justify-content:flex-end; margin-top:16px; padding-top:14px; }
        @media (max-width:700px) { body{padding:10px}.prescription-document{min-height:0;padding:26px 20px}.prescription-meta{grid-template-columns:1fr}.prescription-toolbar,.print-settings-actions{flex-wrap:wrap}.print-settings-actions .btn{flex:1 1 auto} }
        @media print {
            @page { margin:12mm; size:A4 portrait; }
            body { background:#fff; padding:0; }
            .prescription-toolbar,
            .print-settings-overlay,
            .navbar,
            .sidebar,
            .sidepanel,
            .emr-navbar { display:none !important; }
            #printSection, #printSection * { visibility:visible !important; }
            #printSection { display:block !important; left:0 !important; position:absolute !important; top:0 !important; width:100% !important; }
            .prescription-document { border:0; box-shadow:none; margin:0; max-width:none; min-height:0; padding:0; }
            .prescribed-medication { break-inside:avoid; }
        }
    </style>
    <style id="dynamicPrintPage">@media print { @page { size: A4 portrait; margin: 12mm; } }</style>
</head>
<body>
    @php $canExportPrescription = optional(optional(auth()->user())->role)->name !== 'Student'; @endphp
    <nav class="prescription-toolbar" aria-label="Prescription actions">
        <a href="{{ url()->previous() }}" class="btn btn-light">Back</a>
        @if ($canExportPrescription)
            <button type="button" class="btn btn-primary" data-open-print-settings><i class="fa fa-print"></i> Print Prescription</button>
            <a href="{{ route('prescriptions.pdf', $prescription) }}" target="_blank" class="btn btn-light">View PDF</a>
            <a href="{{ route('prescriptions.download', $prescription) }}" class="btn btn-light">Download PDF</a>
        @endif
    </nav>
    <div id="printSection">@include('prescriptions.document')</div>
    @if ($canExportPrescription)
        <section class="print-settings-overlay" data-print-settings aria-hidden="true">
            <div class="print-settings-panel" role="dialog" aria-modal="true" aria-labelledby="printSettingsTitle">
                <h2 id="printSettingsTitle">Print Prescription Settings</h2>
                <div class="print-settings-grid">
                    <label class="print-settings-check"><input type="checkbox" data-print-option="clinicHeader" checked> Include clinic header</label>
                    <label class="print-settings-check"><input type="checkbox" data-print-option="signature" checked> Include doctor signature line</label>
                    <label class="print-settings-check"><input type="checkbox" data-print-option="followUp" {{ $prescription->follow_up_date ? 'checked' : '' }} {{ $prescription->follow_up_date ? '' : 'disabled' }}> Include follow-up date</label>
                    <label class="print-settings-check"><input type="checkbox" data-print-option="instructions" {{ $prescription->additional_instructions ? 'checked' : '' }} {{ $prescription->additional_instructions ? '' : 'disabled' }}> Include additional instructions</label>
                    <div class="print-settings-row"><label for="paperSize">Paper size</label><select id="paperSize" data-print-option="paperSize"><option value="a4">A4</option><option value="letter">Letter</option></select></div>
                    <div class="print-settings-row"><label for="printLayout">Print layout</label><select id="printLayout" data-print-option="layout"><option value="full">Full prescription</option><option value="compact">Compact prescription</option></select></div>
                </div>
                <div class="print-settings-actions">
                    <button type="button" class="btn btn-light" data-print-cancel>Cancel</button>
                    <button type="button" class="btn btn-light" data-print-preview>Preview</button>
                    <button type="button" class="btn btn-primary" data-print-now>Print Now</button>
                </div>
            </div>
        </section>
    @endif
    <script>
    (function () {
        var modal = document.querySelector('[data-print-settings]');
        var openButton = document.querySelector('[data-open-print-settings]');
        var cancelButton = document.querySelector('[data-print-cancel]');
        var previewButton = document.querySelector('[data-print-preview]');
        var printButton = document.querySelector('[data-print-now]');
        var body = document.body;
        if (!modal || !openButton) return;

        function option(name) { return document.querySelector('[data-print-option="' + name + '"]'); }
        function applyOptions() {
            body.classList.toggle('hide-clinic-header', !option('clinicHeader').checked);
            body.classList.toggle('hide-signature', !option('signature').checked);
            body.classList.toggle('hide-follow-up', option('followUp') && !option('followUp').checked);
            body.classList.toggle('hide-additional-instructions', option('instructions') && !option('instructions').checked);
            body.classList.toggle('paper-letter', option('paperSize').value === 'letter');
            body.classList.toggle('layout-compact', option('layout').value === 'compact');
            document.getElementById('dynamicPrintPage').textContent = '@media print { @page { size: ' + (option('paperSize').value === 'letter' ? 'Letter' : 'A4') + ' portrait; margin: 12mm; } }';
        }
        function openModal() { applyOptions(); modal.classList.add('is-visible'); modal.setAttribute('aria-hidden', 'false'); }
        function closeModal() { modal.classList.remove('is-visible'); modal.setAttribute('aria-hidden', 'true'); }

        openButton.addEventListener('click', openModal);
        cancelButton.addEventListener('click', closeModal);
        previewButton.addEventListener('click', function () { applyOptions(); closeModal(); });
        printButton.addEventListener('click', function () { applyOptions(); closeModal(); window.setTimeout(function () { window.print(); }, 100); });
        modal.addEventListener('click', function (event) { if (event.target === modal) closeModal(); });
        document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeModal(); });
        @if ($autoPrint) window.addEventListener('load', openModal); @endif
    })();
    </script>
</body>
</html>
