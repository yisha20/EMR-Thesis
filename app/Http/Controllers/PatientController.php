<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\HealthExaminationRecord;
use App\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $patients = Patient::orderBy('last_name','asc')->paginate(20);

        return view('patients.index', compact('patients'));
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
            ->where('first_name', 'LIKE', '%' . $data['search'] . '%')
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
        
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $request->validate([
                'avatar' => 'image|max:2048|mimes: jpg,jpeg,png',
            ]);

            /** Variables. */
            $avatar = $request->file('avatar');
            $host = $request->getSchemeAndHttpHost();

            /** Uploading the image. */
            $storage = Storage::disk('public');
            $filePath = $storage->putFile('avatars', new File($avatar), 'public');
            $imagePath = $host.'/storage/'.$filePath;
        }
        
        $data = $request->only([
            'id_number',
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'address',
            'phone_number',
            'college_department',
            'type',
            'status',
            'home_address',
            'present_address',
            'age',
            'birthdate',
        ]);

        $data['added_by'] = auth()->user()->id;
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
    public function show($id)
    {
        $patient = Patient::findOrFail($id); // gi edit ko muna para makita ko output pag e click ang view hihi
         
        return view('patients.show', compact('patient'));
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
        
        return view('patients.edit', compact('patient'));
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
        
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $request->validate([
                'avatar' => 'image|max:2048|mimes: jpg,jpeg,png',
            ]);

            /** Variables. */
            $avatar = $request->file('avatar');
            $host = $request->getSchemeAndHttpHost();

            /** Uploading the image. */
            $storage = Storage::disk('public');
            $filePath = $storage->putFile('avatars', new File($avatar), 'public');
            $imagePath = $host.'/storage/'.$filePath;
        }
        
        $data = $request->only([
            'id_number',
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'address',
            'phone_number',
            'college_department',
            'type',
            'status',
            'home_address',
            'present_address',
            'age',
            'birthdate',
        ]);

        $data['updated_by'] = auth()->user()->id;
        $data['avatar'] = $imagePath;
        
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
        
        $examination = HealthExaminationRecord::where('patient_id', $id)->first();
        $examination->update([
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

        ActivityLogger::log(auth()->user()->name . ' restored patient (' . $patient->first_name . ' ' . $patient->last_name . ')');
        
        return redirect()->back()->with('success', 'A patient has been restored successfully!');
    }
}
