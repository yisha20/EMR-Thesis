<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 11mm 15mm; }
        * { box-sizing: border-box; }
        body { color: #111; font-family: DejaVu Serif, serif; font-size: 12px; line-height: 1.35; margin: 0; }
        .certificate { min-height: 175mm; position: relative; }
        .official-header { border-bottom: 1px solid #111; min-height: 24mm; padding: 0 25mm 4mm; position: relative; text-align: center; }
        .official-header img { height: 22mm; object-fit: contain; position: absolute; right: 0; top: 0; width: 22mm; }
        .office-name { color: #a51c30; font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .office-email { color: #111; font-size: 10px; text-decoration: underline; }
        .certificate-title { font-size: 25px; font-weight: normal; letter-spacing: 9px; margin: 7mm 0 5mm; text-align: center; }
        .date-line { margin-left: auto; text-align: left; width: 64mm; }
        .line-value { border-bottom: 1px solid #111; display: inline-block; min-height: 18px; padding: 0 3px 1px; }
        .date-line .line-value { min-width: 44mm; text-align: center; }
        .recipient { font-size: 15px; margin: 5mm 0 4mm; }
        .certify-line { font-size: 14px; margin: 0 0 5mm; }
        .certify-line .name { min-width: 74mm; text-align: center; }
        .certify-line .short { min-width: 25mm; text-align: center; }
        .certify-line .address { min-width: 82mm; text-align: center; }
        .clinical-table { border-collapse: collapse; margin: 0 auto; width: 88%; }
        .clinical-table th { font-size: 13px; padding: 2.5mm 4mm 1mm 0; text-align: left; vertical-align: top; white-space: nowrap; width: 35mm; }
        .clinical-table td { border-bottom: 1px solid #111; min-height: 8mm; padding: 2.5mm 2mm 1mm; vertical-align: top; }
        .clinical-table .checks-cell { border-bottom: 0; padding-bottom: 0; }
        .check-option { display: inline-block; margin-right: 12mm; white-space: nowrap; }
        .box { border: 1px solid #111; display: inline-block; font-family: DejaVu Sans, sans-serif; font-size: 9px; height: 11px; line-height: 9px; margin-right: 4px; text-align: center; vertical-align: middle; width: 11px; }
        .purpose-heading { font-size: 13px; font-weight: bold; margin: 4mm 0 1.5mm 35mm; }
        .purpose-row { margin-left: 35mm; }
        .purpose-row .check-option { margin-bottom: 2mm; margin-right: 9mm; }
        .request-note { font-size: 14px; margin: 9mm 0 0; }
        .signature-block { margin-left: auto; margin-top: 5mm; text-align: center; width: 75mm; }
        .signature-space { height: 13mm; }
        .doctor-name { border-bottom: 1px solid #111; font-size: 13px; font-weight: bold; padding-bottom: 1mm; text-transform: uppercase; }
        .doctor-role, .doctor-license { font-size: 11px; }
        .certificate-number { bottom: 0; color: #555; font-family: DejaVu Sans, sans-serif; font-size: 8px; left: 0; position: absolute; }
    </style>
</head>
<body>
@php
    $purpose = $certificate->purpose;
    $fitness = $certificate->fitness_status;
    $address = $certificate->address_snapshot ?: 'Not provided';
@endphp
<div class="certificate">
    <header class="official-header">
        <div class="office-name">Office of the Medical, Dental and Health Services</div>
        <div class="office-email">mdhso@msuiit.edu.ph</div>
        <img src="{{ public_path('img/msu-iit-logo.png') }}" alt="">
    </header>

    <div class="certificate-title">MEDICAL CERTIFICATE</div>
    <div class="date-line">Date: <span class="line-value">{{ optional($certificate->issue_date)->format('F j, Y') }}</span></div>

    <div class="recipient">To Whom It May Concern:</div>
    <p class="certify-line">
        This is to certify that
        <span class="line-value name">{{ $certificate->patient_name_snapshot }}</span>,
        age/sex <span class="line-value short">{{ $certificate->age_snapshot ?? '—' }} / {{ $certificate->sex_snapshot ?: '—' }}</span>,
        a resident of <span class="line-value address">{{ $address }}</span>.
    </p>

    <table class="clinical-table">
        <tr><th>Reason for Visit:</th><td>{{ $certificate->reason_for_visit }}</td></tr>
        <tr>
            <th></th>
            <td class="checks-cell">
                <span class="check-option"><span class="box">{{ $certificate->consultation_performed ? 'X' : '' }}</span>Consultation</span>
                <span class="check-option"><span class="box">{{ $certificate->physical_examination_performed ? 'X' : '' }}</span>Physical Examination</span>
            </td>
        </tr>
        <tr><th>Impression:</th><td>{{ $certificate->clinical_impression }}</td></tr>
        <tr>
            <th>Fitness:</th>
            <td class="checks-cell">
                <span class="check-option"><span class="box">{{ $fitness === 'physically_fit' ? 'X' : '' }}</span>Physically Fit</span>
                <span class="check-option"><span class="box">{{ $fitness === 'physically_unfit' ? 'X' : '' }}</span>Physically Unfit</span>
                <span class="check-option"><span class="box">{{ in_array($fitness, ['fit_with_restrictions','not_assessed','other'], true) ? 'X' : '' }}</span>Others: {{ in_array($fitness, ['fit_with_restrictions','not_assessed','other'], true) ? ($certificate->fitness_details ?: ucwords(str_replace('_',' ',$fitness))) : '' }}</span>
            </td>
        </tr>
        @if($certificate->remarks)<tr><th>Remarks:</th><td>{{ $certificate->remarks }}</td></tr>@endif
    </table>

    <div class="purpose-heading">Purpose:</div>
    <div class="purpose-row">
        <span class="check-option"><span class="box">{{ $purpose === 'ojt' ? 'X' : '' }}</span>OJT</span>
        <span class="check-option"><span class="box">{{ $purpose === 'scholarship_application' ? 'X' : '' }}</span>Scholarship Application</span>
        <span class="check-option"><span class="box">{{ $purpose === 'employment' ? 'X' : '' }}</span>Employment</span>
        <span class="check-option"><span class="box">{{ !in_array($purpose, ['ojt','scholarship_application','employment'], true) ? 'X' : '' }}</span>Others: {{ !in_array($purpose, ['ojt','scholarship_application','employment'], true) ? ($certificate->purpose_other ?: ucwords(str_replace('_',' ',$purpose))) : '' }}</span>
    </div>

    <p class="request-note">This certification is issued upon the request of the aforementioned.</p>

    <div class="signature-block">
        <div class="signature-space"></div>
        <div class="doctor-name">{{ $certificate->doctor_name_snapshot }}</div>
        <div class="doctor-role">Attending Physician</div>
        <div class="doctor-license">License No: {{ $certificate->doctor_license_number_snapshot ?: 'Not provided' }}</div>
    </div>

    <div class="certificate-number">Certificate No. {{ $certificate->certificate_number }}</div>
</div>
</body>
</html>
