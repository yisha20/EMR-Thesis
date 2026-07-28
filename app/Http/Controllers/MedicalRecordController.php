<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\MedicalRecord;
use App\Patient;
use App\Consultation;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MedicalRecordController extends Controller
{
	public function index(Request $request)
	{
		$search = trim((string) $request->input('search'));
		$date = $request->input('date');

		$medicalRecords = MedicalRecord::with(['patient', 'prescription.patient', 'prescription.doctor'])
			->when($search !== '', function ($query) use ($search) {
				$query->where(function ($recordQuery) use ($search) {
					$recordQuery->where('chief_complaint', 'like', '%' . $search . '%')
						->orWhere('performed_service', 'like', '%' . $search . '%')
						->orWhere('attending_physician', 'like', '%' . $search . '%')
						->orWhereHas('patient', function ($patientQuery) use ($search) {
							$patientQuery->where('id_number', 'like', '%' . $search . '%')
								->orWhere('first_name', 'like', '%' . $search . '%')
								->orWhere('middle_name', 'like', '%' . $search . '%')
								->orWhere('last_name', 'like', '%' . $search . '%');
						});
				});
			})
			->when($date, function ($query) use ($date) {
				$query->whereDate('date_of_consultation', $date);
			})
			->orderByDesc('date_of_consultation')
			->orderByDesc('id')
			->paginate(15)
			->appends($request->query());

		return view('medicalreport.index', compact('medicalRecords'));
	}

	public function edit($id)
	{
        $this->authorizeClinicalMutation();
		$medicalRecord = MedicalRecord::findOrFail($id);
		
		return view('medicalreport.edit', [
			'patient' => $medicalRecord->patient,
			'medicalRecord' => $medicalRecord
		]);
	}

	public function show($id)
	{
		$patient = Patient::with([
			'medicalRecords.counterService.handler',
			'medicalRecords.consultation.doctor',
			'medicalRecords.prescription.patient',
			'medicalRecords.prescription.doctor',
			'medicalRecords.attendingStaff',
		])->findOrFail($id);
		
		return view('medicalreport.show', [
			'patient' => $patient
		]);
	}

    public function doctorPatient(Request $request, Patient $patient)
    {
        $assigned = Consultation::where('patient_id', $patient->id)
            ->where('doctor_id', $request->user()->id)->exists();
        abort_unless(optional($request->user()->role)->name === 'Doctor' && $assigned, 403);

        $patient->load([
            'medicalRecords.counterService.handler',
            'medicalRecords.consultation.doctor',
            'medicalRecords.prescription.patient',
            'medicalRecords.prescription.doctor',
            'medicalRecords.attendingStaff',
        ]);

        return view('medicalreport.show', ['patient' => $patient, 'doctorRecordView' => true]);
    }

	public function store(Request $request)
	{
        $this->authorizeClinicalMutation();

        $data = $this->validatedRecordData($request);
        $data['file'] = $this->storeAttachment($request);
        $data['created_by'] = $request->user()->id;
        $data['attending_staff_id'] = $request->user()->id;

		$medicalRecord = MedicalRecord::create($data);
		ActivityLogger::log('added consultation (' . optional($medicalRecord->patient)->first_name . ' ' . optional($medicalRecord->patient)->last_name . ')');
		
		return redirect()->back()->with('success', 'A new medical record was added.');
	}

	public function update(Request $request, $id)
	{
        $this->authorizeClinicalMutation();

		$medicalRecord = MedicalRecord::findOrFail($id);

        $data = $this->validatedRecordData($request);
        $filePath = $this->storeAttachment($request);
        if ($filePath) {
            $data['file'] = $filePath;
        }
        $data['attending_staff_id'] = $request->user()->id;

		$medicalRecord->update($data);
		ActivityLogger::log('updated consultation (' . optional($medicalRecord->patient)->first_name . ' ' . optional($medicalRecord->patient)->last_name . ')');
		
		return redirect()->back()->with('success', 'A medical record was updated.');
	}

	public function create()
	{
        abort(404);
	}

    public function destroy($id)
    {
        abort_unless(optional(auth()->user()->role)->name === 'Administrator', 403);

        $medicalRecord = MedicalRecord::findOrFail($id);
        $medicalRecord->delete();
        ActivityLogger::log('archived consultation (' . optional($medicalRecord->patient)->first_name . ' ' . optional($medicalRecord->patient)->last_name . ')');

        return redirect()->route('medical-records.index')->with('success', 'Medical record archived.');
    }

    private function authorizeClinicalMutation()
    {
        abort_unless(in_array(optional(auth()->user()->role)->name, ['Administrator', 'Doctor'], true), 403);
    }

    private function validatedRecordData(Request $request)
    {
        return $request->validate([
            'patient_id' => ['nullable', 'integer', Rule::exists('patients', 'id')],
            'performed_service' => 'nullable|string|max:255',
            'date_of_consultation' => 'required|date',
            'time_of_consultation' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'chief_complaint' => 'required|string|max:1000',
            'vital_signs' => 'nullable|array',
            'vital_signs.temperature' => 'nullable|numeric|min:25|max:45',
            'vital_signs.pulse_rate' => 'nullable|integer|min:20|max:250',
            'vital_signs.respiratory_rate' => 'nullable|integer|min:5|max:80',
            'vital_signs.blood_pressure' => 'nullable|string|max:50',
            'vital_signs.weight' => 'nullable|numeric|min:1|max:500',
            'nurse_assigned' => 'nullable|string|max:255',
            'history_of_present_illness' => 'nullable|string|max:5000',
            'medication_taken' => 'nullable|string|max:5000',
            'findings' => 'nullable|string|max:5000',
            'recommendation' => 'nullable|string|max:5000',
            'diagnosis' => 'required|string|max:5000',
            'attending_physician' => 'nullable|string|max:255',
            'file' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);
    }

    private function storeAttachment(Request $request)
    {
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return null;
        }

        $path = Storage::disk('public')->putFile('files', new File($request->file('file')), 'public');

        return Storage::url($path);
    }
}
