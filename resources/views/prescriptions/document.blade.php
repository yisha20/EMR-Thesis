<article class="prescription-document">
    <header class="prescription-header" data-print-section="clinic-header">
        <div class="prescription-logo" aria-label="MSU-IIT Clinic Logo">
            <strong>MSU</strong><span>+</span>
        </div>
        <div>
            <strong>Mindanao State University - Iligan Institute of Technology</strong>
            <h1>MSU-IIT Clinic</h1>
            <span>Iligan City, Philippines</span>
        </div>
    </header>

    <div class="prescription-meta">
        <div><span>Prescription Number</span><strong>{{ $prescription->prescription_number }}</strong></div>
        <div><span>Date</span><strong>{{ $prescription->created_at->format('F j, Y') }}</strong></div>
        <div><span>Doctor</span><strong>{{ $prescription->issuing_doctor_name ?: $prescription->doctor->fullName() }}</strong></div>
        <div><span>Patient</span><strong>{{ trim($prescription->patient->first_name . ' ' . $prescription->patient->middle_name . ' ' . $prescription->patient->last_name) }}</strong></div>
        <div><span>IIT ID Number</span><strong>{{ $prescription->patient->id_number }}</strong></div>
        <div><span>Age / Gender</span><strong>{{ $prescription->patient->age ?: 'N/A' }} / {{ $prescription->patient->gender ?: 'N/A' }}</strong></div>
    </div>

    <section class="prescription-rx">
        <div class="rx-symbol">Rx</div>
        <p class="prescription-type">{{ $prescription->prescription_type }}</p>
        @forelse ($prescription->medications ?: [] as $medication)
            <div class="prescribed-medication">
                <h2>{{ $medication['medication'] }}</h2>
                <dl>
                    <div><dt>Dosage</dt><dd>{{ $medication['dosage'] ?: 'As directed' }}</dd></div>
                    <div><dt>Frequency</dt><dd>{{ $medication['frequency'] ?: 'As directed' }}</dd></div>
                    <div><dt>Duration</dt><dd>{{ $medication['duration'] ?: 'As directed' }}</dd></div>
                    <div><dt>Instructions</dt><dd>{{ $medication['instruction'] ?: 'None' }}</dd></div>
                </dl>
            </div>
        @empty
            <div class="prescribed-medication"><h2>{{ $prescription->prescription_type }}</h2></div>
        @endforelse
    </section>

    @if ($prescription->additional_instructions)
        <section class="prescription-instructions" data-print-section="additional-instructions"><h2>Additional Instructions</h2><p>{!! nl2br(e($prescription->additional_instructions)) !!}</p></section>
    @endif

    @if ($prescription->follow_up_date)
        <p class="prescription-follow-up" data-print-section="follow-up"><strong>Follow-up Date:</strong> {{ $prescription->follow_up_date->format('F j, Y') }}</p>
    @endif

    <footer class="prescription-signature" data-print-section="signature">
        @if (!empty($signatureData) && $prescription->signature_version)
            <img src="{{ $signatureData }}" alt="Verified doctor signature" style="max-height:60px;max-width:200px">
        @endif
        <div class="signature-line"></div>
        <strong>{{ $prescription->issuing_doctor_name ?: $prescription->doctor->fullName() }}</strong>
        <span>{{ $prescription->issuing_doctor_title ?: 'Attending Physician' }}</span>
        @if ($prescription->issuing_doctor_specialty)<span>{{ $prescription->issuing_doctor_specialty }}</span>@endif
        @if ($prescription->issuing_doctor_prc_number)<span>PRC No. {{ $prescription->issuing_doctor_prc_number }}</span>@endif
        @if ($prescription->issuing_doctor_ptr_number)<span>PTR No. {{ $prescription->issuing_doctor_ptr_number }}</span>@endif
        @if (!$prescription->signature_version)<span>Signature on File: No</span>@endif
        <span>MSU-IIT Clinic</span>
    </footer>
</article>
