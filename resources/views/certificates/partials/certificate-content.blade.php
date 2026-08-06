@php
    $purposeLabels = [
        'ojt' => 'OJT',
        'scholarship_application' => 'Scholarship Application',
        'employment' => 'Employment',
        'school_requirement' => 'School Requirement',
        'sports_activity' => 'Sports Participation',
        'return_to_school' => 'Return to School',
        'travel_requirement' => 'Travel Requirement',
        'other' => 'Other',
    ];
    $fitnessLabels = [
        'physically_fit' => 'Physically Fit',
        'physically_unfit' => 'Physically Unfit',
        'fit_with_restrictions' => 'Fit with Restrictions',
        'not_assessed' => 'Other',
        'other' => 'Other',
    ];
    $selectedPurpose = $purposeLabels[$certificate->purpose] ?? 'Other';
    $selectedFitness = $fitnessLabels[$certificate->fitness_status] ?? 'Other';
@endphp

<style>
    @page { size: A4 portrait; margin: 15mm; }
    .medical-certificate-sheet, .medical-certificate-sheet * { box-sizing: border-box; }
    .medical-certificate-sheet { background: #fff; color: #111; font-family: Georgia, "Times New Roman", DejaVu Serif, serif; font-size: 14px; line-height: 1.5; margin: 0 auto; min-height: 267mm; padding: 13mm 15mm 12mm; position: relative; width: 180mm; }
    .mc-official-header { border-bottom: 1px solid #292929; min-height: 27mm; padding: 1mm 22mm 4mm 0; position: relative; text-align: center; }
    .mc-office-name { color: #8f1f2d; font-size: 15px; font-weight: 700; text-transform: uppercase; }
    .mc-office-email { color: #222; font-size: 11px; text-decoration: underline; }
    .mc-logo { height: 21mm; object-fit: contain; position: absolute; right: 0; top: 0; width: 21mm; }
    .mc-logo-placeholder { border: 1px solid #bbb; color: #777; font-family: Arial, sans-serif; font-size: 8px; height: 19mm; padding-top: 7mm; position: absolute; right: 0; text-align: center; top: 0; width: 19mm; }
    .mc-title { font-size: 23px; font-weight: 400; letter-spacing: 6px; margin: 9mm 0 5mm; text-align: center; }
    .mc-date { margin-left: auto; text-align: left; width: 70mm; }
    .mc-line-value { border-bottom: 1px solid #111; display: inline-block; min-height: 20px; padding: 0 3px 1px; }
    .mc-date .mc-line-value { min-width: 48mm; text-align: center; }
    .mc-recipient { font-size: 16px; margin: 8mm 0 5mm; }
    .mc-certification { font-size: 14px; line-height: 2; margin: 0 0 5mm; text-align: justify; }
    .mc-certification .mc-line-value { line-height: 1.4; text-align: center; }
    .mc-name { min-width: 72mm; }
    .mc-short { min-width: 28mm; }
    .mc-address { min-width: 65mm; }
    .mc-field { margin: 5mm 0; }
    .mc-field-label { display: block; font-weight: 700; margin-bottom: 1.5mm; }
    .mc-writing-line { border-bottom: 1px solid #111; min-height: 9mm; padding: 1mm 3mm; white-space: pre-line; }
    .mc-options { margin: 2mm 0 0 8mm; }
    .mc-option { display: inline-block; margin: 0 7mm 2mm 0; white-space: nowrap; }
    .mc-checkbox { border: 1px solid #111; display: inline-block; font-family: Arial, DejaVu Sans, sans-serif; font-size: 9px; height: 12px; line-height: 10px; margin-right: 4px; text-align: center; vertical-align: middle; width: 12px; }
    .mc-details { border-bottom: 1px solid #111; display: inline-block; min-width: 52mm; padding: 0 2mm 1mm; white-space: normal; }
    .mc-request { font-size: 14px; margin-top: 10mm; }
    .mc-signature { margin-left: auto; margin-top: 10mm; text-align: center; width: 78mm; }
    .mc-signature-image { display: block; margin: 0 auto -2mm; max-height: 17mm; max-width: 55mm; }
    .mc-signature-space { height: 14mm; }
    .mc-signature-line { border-bottom: 1px solid #111; }
    .mc-doctor-name { font-size: 13px; font-weight: 700; padding-top: 1mm; text-transform: uppercase; }
    .mc-doctor-role, .mc-doctor-license { font-size: 11px; }
    .mc-metadata { bottom: 7mm; color: #666; font-family: Arial, DejaVu Sans, sans-serif; font-size: 8px; left: 15mm; position: absolute; right: 15mm; }
    .mc-metadata-right { float: right; }
    @media screen and (max-width: 760px) {
        .medical-certificate-sheet { font-size: 12px; min-height: 0; padding: 24px 18px 70px; width: 100%; }
        .mc-office-name { font-size: 12px; }
        .mc-title { font-size: 18px; letter-spacing: 3px; }
        .mc-certification { line-height: 1.8; text-align: left; }
        .mc-line-value, .mc-name, .mc-short, .mc-address { min-width: 0; }
        .mc-option { display: block; margin-bottom: 7px; }
        .mc-signature { width: 68%; }
    }
    @media print {
        html, body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
        .medical-certificate-sheet { box-shadow: none !important; margin: 0 !important; min-height: 267mm; width: 180mm; }
    }
</style>

<article class="medical-certificate-sheet" aria-label="Official medical certificate">
    <header class="mc-official-header">
        <div class="mc-office-name">Office of the Medical, Dental, and Health Services</div>
        <div class="mc-office-email">mdhso@msuiit.edu.ph</div>
        @if(!empty($logoData))<img class="mc-logo" src="{{ $logoData }}" alt="MSU-IIT seal">@else<span class="mc-logo-placeholder">MSU-IIT</span>@endif
    </header>

    <h1 class="mc-title">MEDICAL CERTIFICATE</h1>
    <div class="mc-date">Date: <span class="mc-line-value">{{ optional($certificate->issue_date)->format('F j, Y') ?: now()->format('F j, Y') }}</span></div>

    <div class="mc-recipient">To Whom It May Concern:</div>
    <p class="mc-certification">
        This is to certify that <span class="mc-line-value mc-name">{{ $certificate->patient_name_snapshot }}</span>,
        <span class="mc-line-value mc-short">{{ $certificate->age_snapshot ?? '—' }} years old, {{ $certificate->sex_snapshot ?: '—' }}</span>,
        a resident of <span class="mc-line-value mc-address">{{ $certificate->address_snapshot ?: 'Not provided' }}</span>,
        was seen and evaluated at the MSU-IIT Clinic.
    </p>

    <section class="mc-field">
        <span class="mc-field-label">Reason for Visit:</span>
        <div class="mc-writing-line">{{ $certificate->reason_for_visit }}</div>
        <div class="mc-options">
            <span class="mc-option"><span class="mc-checkbox">{{ $certificate->consultation_performed ? 'X' : '' }}</span>Consultation</span>
            <span class="mc-option"><span class="mc-checkbox">{{ $certificate->physical_examination_performed ? 'X' : '' }}</span>Physical Examination</span>
        </div>
    </section>

    <section class="mc-field">
        <span class="mc-field-label">Impression:</span>
        <div class="mc-writing-line">{{ trim((string) $certificate->clinical_impression) && strtolower(trim($certificate->clinical_impression)) !== 'none' ? $certificate->clinical_impression : 'Not specified' }}</div>
    </section>

    <section class="mc-field">
        <span class="mc-field-label">Remarks / Fitness Assessment:</span>
        <div class="mc-options">
            @foreach(['Physically Fit','Physically Unfit','Fit with Restrictions','Other'] as $label)
                <span class="mc-option"><span class="mc-checkbox">{{ $selectedFitness === $label ? 'X' : '' }}</span>{{ $label }}@if($label === 'Other' && $selectedFitness === 'Other'): <span class="mc-details">{{ $certificate->fitness_details ?: 'Not specified' }}</span>@endif</span>
            @endforeach
        </div>
        @if($certificate->fitness_details && $selectedFitness !== 'Other')<div class="mc-writing-line">{{ $certificate->fitness_details }}</div>@endif
        @if($certificate->remarks)<div class="mc-writing-line">{{ $certificate->remarks }}</div>@endif
    </section>

    <section class="mc-field">
        <span class="mc-field-label">Purpose:</span>
        <div class="mc-options">
            @foreach(['OJT','Scholarship Application','Employment','Other'] as $label)
                <span class="mc-option"><span class="mc-checkbox">{{ ($label === 'Other' ? !in_array($selectedPurpose, ['OJT','Scholarship Application','Employment'], true) : $selectedPurpose === $label) ? 'X' : '' }}</span>{{ $label }}@if($label === 'Other' && !in_array($selectedPurpose, ['OJT','Scholarship Application','Employment'], true)): <span class="mc-details">{{ $certificate->purpose_other ?: $selectedPurpose }}</span>@endif</span>
            @endforeach
        </div>
    </section>

    <p class="mc-request">This certification is issued upon the request of the aforementioned person for the purpose stated above.</p>

    <section class="mc-signature">
        @if(!empty($signatureData))<img class="mc-signature-image" src="{{ $signatureData }}" alt="Verified doctor signature">@else<div class="mc-signature-space"></div>@endif
        <div class="mc-signature-line"></div>
        @if(empty($signatureData))<div class="mc-doctor-role">Doctor Signature</div>@endif
        <div class="mc-doctor-name">{{ $certificate->doctor_name_snapshot }}{{ stripos($certificate->doctor_name_snapshot, 'MD') === false ? ', MD' : '' }}</div>
        <div class="mc-doctor-role">Attending Physician</div>
        <div class="mc-doctor-license">License No.: {{ $certificate->doctor_license_number_snapshot ?: 'Not provided' }}</div>
    </section>

    <footer class="mc-metadata">
        Certificate No. {{ $certificate->certificate_number ?: 'Assigned after saving' }} · Status: {{ ucfirst($certificate->status ?: 'draft') }}
        <span class="mc-metadata-right">Issue Date: {{ optional($certificate->issue_date)->format('Y-m-d') ?: now()->format('Y-m-d') }}</span>
    </footer>
</article>
