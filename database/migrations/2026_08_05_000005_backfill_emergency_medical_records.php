<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
class BackfillEmergencyMedicalRecords extends Migration {
 public function up(){
  DB::transaction(function(){
   DB::table('emergency_encounters as e')->leftJoin('medical_records as m','m.emergency_encounter_id','=','e.id')->whereNull('m.id')->select('e.*')->orderBy('e.id')->chunk(100,function($encounters){
    foreach($encounters as $encounter){
     $arrival=\Illuminate\Support\Carbon::parse($encounter->arrival_at);
     $staff=DB::table('users')->where('id',$encounter->created_by)->first();
     $doctor=$encounter->assigned_doctor_id?DB::table('users')->where('id',$encounter->assigned_doctor_id)->first():null;
     $fullName=function($user){return $user?trim(implode(' ',array_filter([$user->first_name??null,$user->middle_name??null,$user->last_name??null]))):null;};
     DB::table('medical_records')->insert([
      'patient_id'=>$encounter->patient_id,'emergency_encounter_id'=>$encounter->id,'record_type'=>$encounter->encounter_type==='emergency'?'Emergency Encounter':'Walk-in Visit','source'=>'staff_emergency_intake','performed_service'=>$encounter->encounter_type==='emergency'?'Emergency Care':'Walk-in Care','date_of_consultation'=>$arrival->toDateString(),'time_of_consultation'=>$arrival->format('H:i:s'),'chief_complaint'=>$encounter->primary_concern,'symptoms_description'=>$encounter->observed_symptoms,'urgency_level'=>ucfirst($encounter->triage_priority),'consultation_status'=>$encounter->status==='completed'?'Completed':'In Care','description'=>$encounter->initial_notes,'outcome'=>$encounter->status==='completed'?'Emergency care completed':null,'attending_staff_id'=>$encounter->created_by,'created_by'=>$encounter->created_by,'nurse_assigned'=>$fullName($staff),'attending_physician'=>$fullName($doctor),'created_at'=>$encounter->created_at,'updated_at'=>now(),
     ]);
    }
   });
  });
 }
 // Clinical backfills are intentionally preserved on rollback.
 public function down(){}
}
