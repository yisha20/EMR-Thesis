<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { margin: 12mm 11mm 15mm; }
body { color:#111; font-family:DejaVu Sans,sans-serif; font-size:9.5pt; line-height:1.35; margin:0; }
.page-footer { bottom:-10mm; color:#555; font-size:8pt; left:0; position:fixed; right:0; text-align:center; }
.page-footer:after { content:"Page " counter(page); }
.document-header { border-bottom:2px solid #111; margin-bottom:8px; padding-bottom:7px; text-align:center; }
.document-header h1 { font-size:15pt; letter-spacing:.4px; margin:3px 0; }
.document-header h2 { font-size:10.5pt; margin:0; }
.document-meta { font-size:8.5pt; margin:5px 0 8px; text-align:right; }
.section-title { background:#e9e9e9; border:1px solid #222; font-size:10pt; font-weight:bold; margin:10px 0 0; padding:5px 7px; text-transform:uppercase; }
table { border-collapse:collapse; page-break-inside:auto; width:100%; }
thead { display:table-header-group; } tr { page-break-inside:avoid; page-break-after:auto; }
td,th { border:1px solid #333; padding:4px 5px; text-align:left; vertical-align:top; }
th { background:#f4f4f4; font-size:8.5pt; }
.label { color:#333; display:block; font-size:7.5pt; font-weight:bold; text-transform:uppercase; }
.photo-cell { height:100px; text-align:center; vertical-align:middle; width:100px; }
.photo-cell img { height:96px; object-fit:cover; width:96px; }
.muted { color:#555; font-style:italic; }.center { text-align:center; }.nowrap { white-space:nowrap; }
.checkbox { border:1px solid #111; display:inline-block; font-size:7pt; font-weight:bold; height:11px; line-height:10px; margin-right:4px; text-align:center; vertical-align:middle; width:11px; }
.history-grid td { width:33.333%; }.staff-note { background:#fafafa; color:#555; font-style:italic; padding:7px; }
.remarks { min-height:24px; }.signature-space { height:48px; }.page-break { page-break-before:always; }
</style>
</head>
<body>
<div class="page-footer">MSU-IIT Clinic Health Examination Record · </div>
@php
    $p=$assessment->personal_information ?: [];
    $physical=$assessment->physical_examination ?: [];
    $vitals=$assessment->vital_signs ?: [];
    $clinical=$assessment->clinical_assessment ?: [];
    $selectedMedical=$assessment->medicalHistories->keyBy('condition');
    $medicalConditions=\App\Http\Controllers\HealthAssessmentController::MEDICAL_CONDITIONS;
    $systems=['Skin','Head, neck, scalp','Eyes','Ears/nose/throat','Nose/sinuses','Mouth/throat','Neck/lymph/thyroid','Chest/breast/axilla','Lungs','Heart','Abdomen','Back/flank','Anus/rectum','Genitourinary system','Inguinals/genitals','Reflexes','Extremities','Neurologic','Endocrine','Other'];
    $avatar=optional($assessment->account->user)->avatar;
    $avatarPath=$avatar ? parse_url($avatar,PHP_URL_PATH) : null;
    $photoPath=$avatarPath && strpos($avatarPath,'/storage/')===0 ? public_path(ltrim($avatarPath,'/')) : null;
@endphp
<header class="document-header">
    <h2>MINDANAO STATE UNIVERSITY - ILIGAN INSTITUTE OF TECHNOLOGY</h2>
    <div>MSU-IIT Clinic</div><h1>HEALTH EXAMINATION RECORD</h1>
</header>
<div class="document-meta">Record No. {{$assessment->id}} | Version {{$assessment->version}} | Generated {{now()->format('d M Y, H:i')}}</div>

<div class="section-title">1. Patient Information</div>
<table>
    <tr><td><span class="label">Patient name</span>{{trim(($p['last_name']??'').', '.($p['first_name']??'').' '.($p['middle_name']??'').' '.($p['suffix']??''))}}</td><td><span class="label">Patient type</span>{{ucfirst($assessment->patient_type)}}</td><td rowspan="4" class="photo-cell">@if($photoPath && file_exists($photoPath))<img src="{{$photoPath}}" alt="1x1 ID Photo">@else<strong>1x1 ID Photo</strong><br><span class="muted">No photo provided</span>@endif</td></tr>
    <tr><td><span class="label">Student / Faculty / Dependent ID</span>{{$p['opd_number']??optional($assessment->account)->identifier}}</td><td><span class="label">Department</span>{{$p['college_department']??'Not provided'}}</td></tr>
    <tr><td><span class="label">Birth date / Age</span>{{$p['birth_date']??'Not provided'}} / {{$p['age']??'--'}}</td><td><span class="label">Sex / Civil status</span>{{$p['sex']??'Not provided'}} / {{$p['civil_status']??'Not provided'}}</td></tr>
    <tr><td><span class="label">Mobile number</span>{{$p['mobile_number']??'Not provided'}}</td><td><span class="label">Email</span>{{$p['email']??'Not provided'}}</td></tr>
    <tr><td colspan="2"><span class="label">Home address</span>{{$p['home_address']??'Not provided'}}</td><td><span class="label">Examination date</span>{{$p['examination_date']??'Not provided'}}</td></tr>
    <tr><td colspan="3"><span class="label">Present address</span>{{$p['present_address']??'Not provided'}}</td></tr>
    @if($assessment->patient_type==='dependent')<tr><td colspan="3"><span class="label">Sponsor information</span>{{optional(optional($assessment->account)->sponsor)->identifier ?: 'Not linked'}} · {{optional(optional(optional($assessment->account)->sponsor)->user)->name ?: 'Sponsor name unavailable'}}</td></tr>@endif
</table>

<div class="section-title">2. Past Medical History</div>
<table class="history-grid">
@foreach(collect($medicalConditions)->chunk(3) as $row)<tr>@foreach($row as $condition)@php($record=$selectedMedical->get($condition) ?: ($condition==='Other'?$assessment->medicalHistories->first(function($x){return strpos($x->condition,'Other:')===0;}):null))<td><span class="checkbox">{{$record?'X':''}}</span>{{$condition}}@if($record&&($record->notes||$record->medication))<br><small>{{$record->notes}} {{$record->medication?'Medication: '.$record->medication:''}}</small>@endif</td>@endforeach @for($i=$row->count();$i<3;$i++)<td></td>@endfor</tr>@endforeach
</table>

<div class="section-title">3. Women's Health (when applicable)</div>
<table><tr><td><span class="label">Last menstrual period</span>{{data_get($assessment,'womens_health.last_menstrual_period','Not provided / not applicable')}}</td><td><span class="label">Menstrual pattern</span>{{data_get($assessment,'womens_health.menstrual_pattern','Not provided / not applicable')}}</td></tr></table>

<div class="section-title">4. Family History</div>
<table class="history-grid"><tr>@forelse($assessment->familyHistories as $index=>$item)@if($index&&$index%3===0)</tr><tr>@endif<td><span class="checkbox">X</span>{{$item->condition}}{{$item->relationship?' - '.$item->relationship:''}}{{$item->details?': '.$item->details:''}}</td>@empty<td>None reported</td>@endforelse</tr></table>

<div class="section-title">5. Social History and Current Medications</div>
<table><tr><td><span class="label">Smoking</span>{{data_get($assessment,'social_history.smoking_status','Not reported')}}</td><td><span class="label">Alcohol</span>{{data_get($assessment,'social_history.drinks_alcohol','Not reported')}} · {{data_get($assessment,'social_history.alcohol_type','')}} {{data_get($assessment,'social_history.alcohol_frequency','')}}</td></tr><tr><td colspan="2"><span class="label">Current medications</span>{{$assessment->medications->pluck('medication')->implode(', ') ?: 'None reported'}}</td></tr></table>

<div class="section-title">6. Registered Dependents</div>
<table><thead><tr><th>Name</th><th>Relationship</th><th>Birth date</th><th>Verification</th></tr></thead><tbody>@forelse(optional($assessment->account)->dependents ?: [] as $dependent)<tr><td>{{$dependent->full_name}}</td><td>{{$dependent->relationship}}</td><td>{{optional($dependent->birth_date)->format('d M Y')}}</td><td>{{str_replace('_',' ',ucfirst($dependent->verification_status))}}</td></tr>@empty<tr><td colspan="4">No registered dependents.</td></tr>@endforelse</tbody></table>

<div class="page-break"></div>
<div class="section-title">7. Physical Examination - Clinic Staff Only</div>
@if(empty($physical))<div class="staff-note">For clinic staff completion</div>@endif
<table><thead><tr><th>Body / System</th><th class="center">Normal</th><th class="center">Abnormal</th><th class="center">Not Examined</th><th>Remarks</th></tr></thead><tbody>
@foreach($systems as $system)@php($exam=$physical[$system]??$physical[\Illuminate\Support\Str::slug($system,'_')]??[])<tr><td>{{$system}}</td>@foreach(['normal','abnormal','not_examined'] as $state)<td class="center"><span class="checkbox">{{data_get($exam,'status')===$state?'X':''}}</span></td>@endforeach<td>{{$exam['remarks']??''}}</td></tr>@endforeach
</tbody></table>

<div class="section-title">8. Vital Signs - Clinic Staff Only</div>
@if(empty($vitals))<div class="staff-note">For clinic staff completion</div>@endif
<table><thead><tr><th>Temperature</th><th>Pulse</th><th>Respiratory Rate</th><th>Blood Pressure</th><th>Weight</th><th>Height</th><th>BMI</th></tr></thead><tr><td>{{$vitals['temperature']??'--'}} °C</td><td>{{$vitals['pulse_rate']??'--'}} bpm</td><td>{{$vitals['respiratory_rate']??'--'}} /min</td><td>{{($vitals['blood_pressure_systolic']??'--').'/'.($vitals['blood_pressure_diastolic']??'--')}} mmHg</td><td>{{$vitals['weight']??'--'}} kg</td><td>{{$vitals['height']??'--'}} cm</td><td>{{$vitals['bmi']??'--'}}</td></tr></table>

<div class="section-title">9. Nursing Interventions</div>
<table><thead><tr><th>Intervention</th><th>Date</th><th>Time</th><th>Performed By</th><th>Notes</th></tr></thead><tbody>@forelse($assessment->nursingInterventions as $item)<tr><td>{{$item->intervention}}</td><td>{{optional($item->intervention_date)->format('d M Y')}}</td><td>{{$item->intervention_time}}</td><td>{{optional(\App\User::find($item->performed_by))->name}}</td><td>{{$item->notes}}</td></tr>@empty<tr><td colspan="5" class="staff-note">For clinic staff completion</td></tr>@endforelse</tbody></table>

<div class="section-title">10. Assessment and Recommendations - Physician Only</div>
@if(empty($clinical))<div class="staff-note">For authorized physician completion</div>@endif
<table><tr><td><span class="checkbox">{{data_get($clinical,'fitness_status')==='physically_fit'?'X':''}}</span>Physically Fit</td><td><span class="checkbox">{{data_get($clinical,'fitness_status')==='not_physically_fit'?'X':''}}</span>Not Physically Fit</td><td><span class="checkbox">{{data_get($clinical,'fitness_status')==='fit_with_restrictions'?'X':''}}</span>Fit with Restrictions</td><td><span class="checkbox">{{data_get($clinical,'fitness_status')==='further_evaluation'?'X':''}}</span>Further Evaluation Required</td></tr>
<tr><td colspan="4"><span class="label">Assessment</span><div class="remarks">{{$clinical['assessment']??''}}</div></td></tr><tr><td colspan="4"><span class="label">Recommendations</span><div class="remarks">{{$clinical['recommendations']??''}}</div></td></tr>
<tr><td colspan="2"><span class="label">Examiner / Physician</span>{{$clinical['examiner_name']??''}}<div class="signature-space"></div><span class="label">Signature</span></td><td><span class="label">License number</span>{{$clinical['license_number']??''}}</td><td><span class="label">Date of examination</span>{{$clinical['examination_date']??''}}</td></tr></table>
</body>
</html>
