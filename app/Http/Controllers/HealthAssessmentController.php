<?php

namespace App\Http\Controllers;

use App\HealthAssessment;
use App\Models\ActivityLog;
use App\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthAssessmentController extends Controller
{
    const MEDICAL_CONDITIONS = ['Allergies','Amoebiasis','Anemia','Arthritis','Back and joint pains','Previous fracture','Asthma','Chest pains','Chicken pox','Diabetes mellitus','Epilepsy','Eye or ear problem','Gallbladder stone','Goiter','Gout','Hemorrhoids','Hepatitis A/B/C','Hyperacidity or ulcer','Hypertension','Kidney or bladder stones','Loss of consciousness','Measles','Mumps','Pneumonia','Prostate problems','Seizure','Sinusitis or allergic rhinitis','Skin disorders','STI/HIV','Stroke','Surgery or injury','Thyroid problems','Tonsillitis','Tuberculosis','Urinary tract infection','Other'];
    const FAMILY_CONDITIONS = ['High blood pressure','Tuberculosis','Heart disease','Asthma','Diabetes','Allergies','Cancer','Other hereditary disease'];

    public function edit(Request $request)
    {
        $account = $request->user()->patientAccount;
        abort_unless($account, 403);
        $assessment = $account->latestAssessment()->with(['medicalHistories','familyHistories','medications'])->first();
        return view('patient.assessments.form', ['account'=>$account, 'assessment'=>$assessment, 'medicalConditions'=>self::MEDICAL_CONDITIONS, 'familyConditions'=>self::FAMILY_CONDITIONS]);
    }

    public function show(Request $request, HealthAssessment $assessment)
    {
        abort_unless(in_array(optional($request->user()->role)->name, ['Administrator','Nurse','Doctor','Staff'], true), 403);
        $assessment->load(['account.user','account.dependents','medicalHistories','familyHistories','medications','nursingInterventions']);
        return view('patient.assessments.show', compact('assessment'));
    }

    public function save(Request $request)
    {
        return $this->persist($request, false);
    }

    public function submit(Request $request)
    {
        return $this->persist($request, true);
    }

    private function persist(Request $request, $submit)
    {
        $account = $request->user()->patientAccount;
        abort_unless($account, 403);
        $required = $submit ? 'required' : 'nullable';
        $photoRequired = $submit && ! $request->user()->avatar ? 'required' : 'nullable';
        $rules = [
            'formal_photo'=>$photoRequired.'|image|mimes:jpg,jpeg,png|max:4096',
            'opd_number'=>'nullable|string|max:50','examination_date'=>$required.'|date',
            'college_department'=>$required.'|string|max:255','last_name'=>$required.'|string|max:100',
            'first_name'=>$required.'|string|max:100','middle_name'=>'nullable|string|max:100','suffix'=>'nullable|string|max:20',
            'home_address'=>$required.'|string|max:500','present_address'=>$required.'|string|max:500',
            'sex'=>$required.'|string|max:30','birth_date'=>$required.'|date|before_or_equal:today',
            'civil_status'=>$required.'|string|max:50','mobile_number'=>$required.'|string|max:50','email'=>$required.'|email',
            'medical_conditions'=>'array','medical_conditions.*'=>'in:'.implode(',', self::MEDICAL_CONDITIONS),
            'medical_details'=>'array','other_medical_condition'=>'nullable|string|max:500',
            'family_conditions'=>'array','family_conditions.*'=>'in:'.implode(',', self::FAMILY_CONDITIONS),
            'family_details'=>'array','other_family_condition'=>'nullable|string|max:255',
            'smoking_status'=>$required.'|in:Never,Current smoker,Former smoker',
            'smoking_packs'=>($submit ? 'required_if:smoking_status,Current smoker' : 'nullable').'|numeric|min:0|max:100',
            'drinks_alcohol'=>$required.'|in:No,Yes','alcohol_type'=>'nullable|required_if:drinks_alcohol,Yes|string|max:100',
            'alcohol_frequency'=>'nullable|required_if:drinks_alcohol,Yes|in:Occasional,Seldom',
            'takes_medications'=>$required.'|in:No,Yes',
            'medications'=>'array','medications.*'=>'nullable|string|max:255',
            'last_menstrual_period'=>'nullable|date','menstrual_pattern'=>'nullable|in:Regular,Irregular,Prefer not to answer',
        ];
        $data = $request->validate($rules);
        if ($submit && in_array('Other', $data['medical_conditions'] ?? [], true)) {
            $request->validate(['other_medical_condition'=>'required|string|max:500']);
        }
        if ($submit && in_array('Other hereditary disease', $data['family_conditions'] ?? [], true)) {
            $request->validate(['other_family_condition'=>'required|string|max:255']);
        }
        if ($submit && ($data['takes_medications'] ?? 'No') === 'Yes'
            && empty(array_filter($data['medications'] ?? []))) {
            $request->validate(['medications.0'=>'required|string|max:255']);
        }

        $photoUrl = null;
        if ($request->hasFile('formal_photo') && $request->file('formal_photo')->isValid()) {
            $photoPath = Storage::disk('public')->putFile('avatars', $request->file('formal_photo'), 'public');
            $photoUrl = Storage::disk('public')->url($photoPath);
        }

        DB::transaction(function () use ($request, $account, $data, $submit, $photoUrl) {
            $assessment = $account->latestAssessment()->first();
            if (! $assessment || in_array($assessment->status, ['patient_submitted','under_review','clinically_completed'], true)) {
                $assessment = new HealthAssessment(['version'=>($assessment ? $assessment->version + 1 : 1)]);
                $assessment->patient_account_id = $account->id;
            }
            $birthDate = ! empty($data['birth_date']) ? new \DateTime($data['birth_date']) : null;
            $personal = collect($data)->only(['opd_number','examination_date','college_department','last_name','first_name','middle_name','suffix','home_address','present_address','sex','birth_date','civil_status','mobile_number','email'])->all();
            $personal['age'] = $birthDate ? $birthDate->diff(new \DateTime('today'))->y : null;
            $assessment->fill([
                'patient_id'=>$account->patient_id, 'patient_type'=>$account->patient_type,
                'status'=>$submit ? 'patient_submitted' : 'draft',
                'submitted_at'=>$submit ? now() : null, 'personal_information'=>$personal,
                'womens_health'=>collect($data)->only(['last_menstrual_period','menstrual_pattern'])->all(),
                'social_history'=>collect($data)->only(['smoking_status','smoking_packs','drinks_alcohol','alcohol_type','alcohol_frequency','takes_medications'])->all(),
            ])->save();

            $assessment->medicalHistories()->delete();
            foreach ($data['medical_conditions'] ?? [] as $condition) {
                $details = $data['medical_details'][$condition] ?? [];
                $assessment->medicalHistories()->create([
                    'condition'=>$condition === 'Other' ? 'Other: '.$data['other_medical_condition'] : $condition,
                    'diagnosis_date'=>$details['diagnosis_date'] ?? null,'current_status'=>$details['current_status'] ?? null,
                    'medication'=>$details['medication'] ?? null,'notes'=>$details['notes'] ?? null,
                ]);
            }
            $assessment->familyHistories()->delete();
            foreach ($data['family_conditions'] ?? [] as $condition) {
                $details = $data['family_details'][$condition] ?? [];
                $assessment->familyHistories()->create([
                    'condition'=>$condition,
                    'relationship'=>$details['relationship'] ?? null,
                    'details'=>$condition === 'Other hereditary disease'
                        ? ($data['other_family_condition'] ?? null)
                        : ($details['details'] ?? null),
                ]);
            }
            $assessment->medications()->delete();
            foreach (array_filter($data['medications'] ?? []) as $order=>$medication) {
                $assessment->medications()->create(['medication'=>$medication,'display_order'=>$order]);
            }
            if ($submit && ! $account->patient_id) {
                $patient = Patient::create([
                    'id_number'=>$account->identifier,'first_name'=>$personal['first_name'],'middle_name'=>$personal['middle_name'] ?? null,
                    'last_name'=>$personal['last_name'],'gender'=>$personal['sex'],'phone_number'=>$personal['mobile_number'],
                    'college_department'=>$personal['college_department'],'type'=>ucfirst($account->patient_type),'status'=>'Active',
                    'home_address'=>$personal['home_address'],'present_address'=>$personal['present_address'],'age'=>$personal['age'],
                    'birthdate'=>$personal['birth_date'],'avatar'=>$photoUrl ?: $request->user()->avatar,
                    'added_by'=>$request->user()->id,
                ]);
                $account->patient_id = $patient->id;
                $assessment->patient_id = $patient->id;
                $assessment->save();
            }
            if ($photoUrl) {
                $request->user()->update(['avatar'=>$photoUrl]);
                if ($account->patient_id) {
                    Patient::whereKey($account->patient_id)->update(['avatar'=>$photoUrl]);
                }
            }
            $account->update(['health_assessment_status'=>$assessment->status,'health_assessment_completed_at'=>$submit ? now() : null]);
            ActivityLog::create(['user_id'=>$request->user()->id,'action'=>$submit ? 'Health assessment submitted' : 'Health assessment draft saved','description'=>'Assessment #'.$assessment->id.'; clinical content omitted.']);
        });
        return redirect()->route($submit ? 'student.dashboard' : 'patient.assessment.edit')->with('success', $submit ? 'Health assessment submitted.' : 'Draft saved.');
    }

    public function pdf(Request $request, HealthAssessment $assessment)
    {
        $account = optional($request->user())->patientAccount;
        $role = optional(optional($request->user())->role)->name;
        $owns = $account && ($assessment->patient_account_id === $account->id || $account->dependents()->where('patient_account_id', $assessment->patient_account_id)->exists());
        abort_unless($owns || in_array($role, ['Administrator','Nurse','Doctor','Staff'], true), 403);
        $assessment->load(['account.user','account.sponsor.user','account.dependents','medicalHistories','familyHistories','medications','nursingInterventions']);
        ActivityLog::create(['user_id'=>$request->user()->id,'action'=>'Health assessment PDF downloaded','description'=>'Assessment #'.$assessment->id]);
        return Pdf::loadView('patient.assessments.pdf', compact('assessment'))->setPaper('a4')->download('health-assessment-'.$assessment->id.'.pdf');
    }
}
