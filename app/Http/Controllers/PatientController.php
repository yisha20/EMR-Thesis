<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\HealthExaminationRecord;
use App\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $patients = Patient::with('patientAccount')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('middle_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('id_number', 'LIKE', "%{$search}%")
                        ->orWhere('college_department', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->filled('gender'), function ($query) use ($request) {
                $query->where('gender', $request->gender);
            })
            ->when($request->filled('department'), function ($query) use ($request) {
                $query->where('college_department', $request->department);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('last_name', 'asc')
            ->paginate(20)
            ->appends($request->query());

        $departments = Patient::whereNotNull('college_department')
            ->distinct()
            ->orderBy('college_department')
            ->pluck('college_department');

        return view('patients.index', compact('patients', 'departments'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function archive()
    {
        $patients = Patient::withTrashed()
            ->where('deleted_at', '!=', null)
            ->orderBy('last_name','asc')
            ->paginate(20);

        return view('patients.archive', compact('patients'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Searches for a patient from DB.
     * 
     * @return Collection
     */
    public function search()
    {
        $data = request()->validate([
            'search' => 'required',
        ]);
        
        $patients = Patient::where('first_name', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('id_number', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('first_name', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('middle_name', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('last_name', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('gender', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('phone_number', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('college_department', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('type', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('status', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('home_address', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('present_address', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('age', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('birthdate', 'LIKE', '%' . $data['search'] . '%')
            ->paginate(20);

        return view('patients.search', [
            'patients' => $patients
        ]);
    }

    /**
     * Searches for a patient from DB.
     * 
     * @return Collection
     */
    public function archive_search()
    {
        $data = request()->validate([
            'search' => 'required',
        ]);
        
        $patients = Patient::onlyTrashed()
            ->where(function ($query) use ($data) {
                $query->where('first_name', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('id_number', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('middle_name', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('gender', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('phone_number', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('college_department', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('type', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('status', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('home_address', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('present_address', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('age', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('birthdate', 'LIKE', '%' . $data['search'] . '%');
            })
            ->paginate(20);

        return view('patients.archive_search', [
            'patients' => $patients
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return dd($request->all());
        $request->validate([
            'id_number' => 'nullable|string|max:255|unique:patients,id_number',
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'phone_number' => 'required',
            'college_department' => 'required',
            'home_address' => 'required',
            'present_address' => 'required',
            'age' => 'required',
            'birthdate' => 'required',
        ]);
        
        $imagePath = null;

        $request->validate([
            'avatar' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            /** Variables. */
            $avatar = $request->file('avatar');

            /** Uploading the image. */
            $storage = Storage::disk('public');
            $filePath = $storage->putFile('avatars', new File($avatar), 'public');
            $imagePath = Storage::url($filePath);
        }
        
        $data = $request->only([
            'id_number',
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'address',
            'phone_number',
            'email',
            'college_department',
            'type',
            'civil_status',
            'home_address',
            'present_address',
            'age',
            'birthdate',
        ]);

        $data['added_by'] = auth()->user()->id;
        $data['updated_by'] = auth()->user()->id;
        $data['status'] = 'Active';
        $data['date_registered'] = now();
        $data['avatar'] = $imagePath;
        
        $patient = Patient::create($data);

        ActivityLogger::log(auth()->user()->name . ' added a new patient (' . $patient->first_name . ' ' . $patient->last_name . ')');
        
        $pastMedicalHistory = $request->only([
            'pastmedical_history',
            'last_menstrual_period',
            'menstrual_pattern',
        ]);
        $familyHistory = $request->only('family_history');
        $socialHistory = $request->only([
            'is_smoking',
            'packs_smoked',
            'is_drinking_beer',
            'drinking_frequency',
            'is_taking_medication',
            'medications',
        ]);
        $physicalExamination = $request->only([
            'skin_status',
            'skin_remarks',
            'head_status',
            'head_remarks',
            'eyes_status',
            'eyes_remarks',
            'ears_status',
            'ears_remarks',
            'nose_status',
            'nose_remarks',
            'mouth_status',
            'mouth_remarks',
            'neck_status',
            'neck_remarks',
            'chest_status',
            'chest_remarks',
            'lungs_status',
            'lungs_remarks',
            'heart_status',
            'heart_remarks',
            'abdomen_status',
            'abdomen_remarks',
            'back_status',
            'back_remarks',
            'anus_status',
            'anus_remarks',
            'gu_system_status',
            'gu_system_remarks',
            'genitals_status',
            'genitals_remarks',
            'reflexes_status',
            'reflexes_remarks',
            'extremities_status',
            'extremities_remarks',
            'neurologic_status',
            'neurologic_remarks',
            'endocrine_status',
            'endocrine_remarks',
            'others_status',
            'others_remarks',
        ]);
        $vitalSigns = $request->only([
            'temperature',
            'pulse_rate',
            'respiratory_rate',
            'blood_pressure',
            'weight',
        ]);
        $nursingIntervention = $request->only('nursing_interventions');
        $assesment = $request->only([
            'physically_fit',
            'physically_fit_description',
            'date_examined',
            'by',
            'license_no',
        ]);
        
        $examination = HealthExaminationRecord::create([
            'patient_id' => $patient->id,
            'past_medical_history_others' => $request->past_medical_history_others,
            'family_history_others' => $request->family_history_others,
            'past_medical_history' => $pastMedicalHistory,
            'family_history' => $familyHistory,
            'social_history' => $socialHistory,
            'phyiscal_examination' => $physicalExamination,
            'vital_signs' => $vitalSigns,
            'assessment' => $assesment,
            'nursing_interventions' => $nursingIntervention,
            'added_by' => auth()->user()->id,
        ]);
        
        return redirect()->back()->with('success', 'A new patient has been created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        if (optional($request->user()->role)->name === 'Doctor') {
            abort_unless(\App\Consultation::where('patient_id', $patient->id)
                ->where('doctor_id', $request->user()->id)->exists(), 403);
        }
        $this->normalizeHealthExaminationRecord($patient);
         
        return view('patients.show', compact('patient'));
    }

    private function normalizeHealthExaminationRecord(Patient $patient)
    {
        $record = $patient->healthExaminationRecord;
        if (!$record) {
            return;
        }

        $normalized = $this->normalizedHealthRecordData($record);

        foreach ($normalized as $field => $value) {
            $record->setAttribute($field, $value);
        }
    }

    private function normalizedHealthRecordData(HealthExaminationRecord $record)
    {
        $defaults = $this->healthRecordDefaults();
        $normalized = [];

        foreach ($defaults as $field => $fieldDefaults) {
            $normalized[$field] = array_merge(
                $fieldDefaults,
                $this->normalizeArrayValue($record->{$field})
            );
        }

        $normalized['past_medical_history']['pastmedical_history'] = $this->normalizeListValue(
            $normalized['past_medical_history']['pastmedical_history']
        );
        $normalized['family_history']['family_history'] = $this->normalizeListValue(
            $normalized['family_history']['family_history']
        );
        $normalized['social_history']['medications'] = $this->normalizeListValue(
            $normalized['social_history']['medications']
        );
        $normalized['social_history']['allergies'] = $this->normalizeListValue(
            $normalized['social_history']['allergies']
        );
        $normalized['nursing_interventions']['nursing_interventions'] = array_map(
            function ($intervention) {
                if (!is_array($intervention)) {
                    $intervention = ['intervention' => $intervention];
                }

                return array_merge([
                    'intervention' => '',
                    'time' => '',
                    'by' => '',
                ], $intervention);
            },
            $this->normalizeListValue($normalized['nursing_interventions']['nursing_interventions'])
        );

        return $normalized;
    }

    private function healthRecordDefaults()
    {
        $physicalExamination = [];
        foreach ([
            'skin', 'head', 'eyes', 'ears', 'nose', 'mouth', 'neck', 'chest',
            'lungs', 'heart', 'abdomen', 'back', 'anus', 'gu_system',
            'genitals', 'reflexes', 'extremities', 'neurologic', 'endocrine',
            'others',
        ] as $section) {
            $physicalExamination[$section . '_status'] = '';
            $physicalExamination[$section . '_remarks'] = '';
        }

        return [
            'past_medical_history' => [
                'pastmedical_history' => [],
                'last_menstrual_period' => '',
                'menstrual_pattern' => '',
            ],
            'family_history' => [
                'family_history' => [],
            ],
            'social_history' => [
                'is_smoking' => '',
                'packs_smoked' => '',
                'is_drinking_beer' => '',
                'drinking_frequency' => '',
                'is_taking_medication' => '',
                'medications' => [],
                'allergies' => [],
                'exercise' => '',
                'diet' => '',
            ],
            'phyiscal_examination' => $physicalExamination,
            'vital_signs' => [
                'temperature' => '',
                'pulse_rate' => '',
                'respiratory_rate' => '',
                'blood_pressure' => '',
                'weight' => '',
            ],
            'assessment' => [
                'physically_fit' => '',
                'physically_fit_description' => '',
                'date_examined' => '',
                'by' => '',
                'license_no' => '',
            ],
            'nursing_interventions' => [
                'nursing_interventions' => [],
            ],
        ];
    }

    private function normalizeArrayValue($value)
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($decoded) ? $decoded : [$decoded];
            }

            return strpos($value, ',') !== false
                ? array_values(array_filter(array_map('trim', explode(',', $value)), 'strlen'))
                : [$value];
        }

        return [$value];
    }

    private function normalizeListValue($value)
    {
        return array_values($this->normalizeArrayValue($value));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $patient = Patient::findOrFail($id);
        $record = HealthExaminationRecord::firstOrCreate(
            ['patient_id' => $patient->id],
            array_merge($this->healthRecordDefaults(), [
                'added_by' => auth()->id(),
            ])
        );
        $record->update($this->normalizedHealthRecordData($record));
        $patient->load('healthExaminationRecord');
        $this->normalizeHealthExaminationRecord($patient);

        $socialHistory = $patient->getSocialHistory();
        $physicalExamination = $patient->getPhysicalExamination();

        return view('patients.edit', compact(
            'patient',
            'socialHistory',
            'physicalExamination'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $imagePath = null;

        $request->validate([
            'id_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('patients', 'id_number')->ignore($id),
            ],
            'avatar' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            /** Variables. */
            $avatar = $request->file('avatar');

            /** Uploading the image. */
            $storage = Storage::disk('public');
            $filePath = $storage->putFile('avatars', new File($avatar), 'public');
            $imagePath = Storage::url($filePath);
        }
        
        $data = $request->only([
            'id_number',
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'address',
            'phone_number',
            'email',
            'college_department',
            'type',
            'civil_status',
            'home_address',
            'present_address',
            'age',
            'birthdate',
        ]);

        $data['updated_by'] = auth()->user()->id;
        $data['status'] = $request->input('patient_status', 'Active');

        if ($imagePath) {
            $data['avatar'] = $imagePath;
        }
        
        $patient = Patient::find($id);
        $patient->update($data);

        ActivityLogger::log(auth()->user()->name . ' updated a patient record (' . $patient->first_name . ' ' . $patient->last_name . ')');
        
        $pastMedicalHistory = $request->only([
            'pastmedical_history',
            'last_menstrual_period',
            'menstrual_pattern',
        ]);
        $familyHistory = $request->only('family_history');
        $socialHistory = $request->only([
            'is_smoking',
            'packs_smoked',
            'is_drinking_beer',
            'drinking_frequency',
            'is_taking_medication',
            'medications',
        ]);
        $physicalExamination = $request->only([
            'skin_status',
            'skin_remarks',
            'head_status',
            'head_remarks',
            'eyes_status',
            'eyes_remarks',
            'ears_status',
            'ears_remarks',
            'nose_status',
            'nose_remarks',
            'mouth_status',
            'mouth_remarks',
            'neck_status',
            'neck_remarks',
            'chest_status',
            'chest_remarks',
            'lungs_status',
            'lungs_remarks',
            'heart_status',
            'heart_remarks',
            'abdomen_status',
            'abdomen_remarks',
            'back_status',
            'back_remarks',
            'anus_status',
            'anus_remarks',
            'gu_system_status',
            'gu_system_remarks',
            'genitals_status',
            'genitals_remarks',
            'reflexes_status',
            'reflexes_remarks',
            'extremities_status',
            'extremities_remarks',
            'neurologic_status',
            'neurologic_remarks',
            'endocrine_status',
            'endocrine_remarks',
            'others_status',
            'others_remarks',
        ]);
        $vitalSigns = $request->only([
            'temperature',
            'pulse_rate',
            'respiratory_rate',
            'blood_pressure',
            'weight',
        ]);
        $nursingIntervention = $request->only('nursing_interventions');
        $assesment = $request->only([
            'physically_fit',
            'physically_fit_description',
            'date_examined',
            'by',
            'license_no',
        ]);
        
        $examination = HealthExaminationRecord::firstOrCreate(
            ['patient_id' => $id],
            array_merge($this->healthRecordDefaults(), [
                'added_by' => auth()->user()->id,
            ])
        );
        $existingHealthData = $this->normalizedHealthRecordData($examination);
        $examination->update([
            'patient_id' => $patient->id,
            'past_medical_history_others' => $request->past_medical_history_others,
            'family_history_others' => $request->family_history_others,
            'past_medical_history' => array_merge(
                $this->healthRecordDefaults()['past_medical_history'],
                $pastMedicalHistory
            ),
            'family_history' => array_merge(
                $this->healthRecordDefaults()['family_history'],
                $familyHistory
            ),
            'social_history' => array_merge(
                $this->healthRecordDefaults()['social_history'],
                ['allergies' => $existingHealthData['social_history']['allergies']],
                $socialHistory
            ),
            'phyiscal_examination' => array_merge(
                $this->healthRecordDefaults()['phyiscal_examination'],
                $physicalExamination
            ),
            'vital_signs' => array_merge(
                $this->healthRecordDefaults()['vital_signs'],
                $vitalSigns
            ),
            'assessment' => array_merge(
                $this->healthRecordDefaults()['assessment'],
                $assesment
            ),
            'nursing_interventions' => array_merge(
                $this->healthRecordDefaults()['nursing_interventions'],
                $nursingIntervention
            ),
            'added_by' => auth()->user()->id,
        ]);
        
        return redirect()->back()->with('success', 'A new patient has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->archived_by = auth()->id();
        $patient->save();
        $patient->delete();

        ActivityLogger::log(auth()->user()->name . ' archived patient (' . $patient->first_name . ' ' . $patient->last_name . ')');

        return redirect()->back()->with('success', 'A patient has been archived.');
    }

    public function deletePatient($id)
    {
        $patient = Patient::withTrashed()->where('id', $id)->first();

        if(!isset($patient)) {
            return redirect()->back()->with('error', 'The system was unable to delete the patient permanently.');
        }

        $patient->forceDelete();

        ActivityLogger::log(auth()->user()->name . ' permanently deleted patient (' . $patient->first_name . ' ' . $patient->last_name . ')');
        
        return redirect()->back()->with('success', 'A patient has been deleted permanently.');
    }

    public function restorePatient($id)
    {
        $patient = Patient::withTrashed()->where('id', $id)->first();

        if(!isset($patient)) {
            return redirect()->back()->with('error', 'The system was unable to restore the patient.');
        }

        $patient->restore();
        $patient->archived_by = null;
        $patient->save();

        ActivityLogger::log(auth()->user()->name . ' restored patient (' . $patient->first_name . ' ' . $patient->last_name . ')');
        
        return redirect()->back()->with('success', 'A patient has been restored successfully!');
    }
}
