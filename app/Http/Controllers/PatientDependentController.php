<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\PatientDependent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientDependentController extends Controller
{
    private $relationships=['Spouse','Child','Parent','Sibling','Legal Guardian','Other immediate family member'];
    public function index(Request $request){$account=$request->user()->patientAccount;abort_unless($account&&in_array($account->patient_type,['student','faculty']),403);$dependents=$account->dependents()->latest()->get();return view('patient.dependents.index',['account'=>$account,'dependents'=>$dependents,'relationships'=>$this->relationships]);}
    public function store(Request $request){
        $account=$request->user()->patientAccount;abort_unless($account&&in_array($account->patient_type,['student','faculty']),403);
        $data=$request->validate(['full_name'=>'required|string|max:255','relationship'=>'required|in:'.implode(',',$this->relationships),'relationship_details'=>'nullable|required_if:relationship,Other immediate family member|string|max:255','birth_date'=>'required|date|before_or_equal:today','sex'=>'required|string|max:30','civil_status'=>'nullable|string|max:50','home_address'=>'required|string|max:500','contact_number'=>'nullable|string|max:50','emergency_contact'=>'required|string|max:255','proof'=>'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf']);
        $duplicate=$account->dependents()->whereRaw('LOWER(full_name)=?',[strtolower($data['full_name'])])->where('birth_date',$data['birth_date'])->whereIn('verification_status',['pending_verification','verified'])->exists();
        abort_if($duplicate,422,'A matching dependent is already registered.');
        if($request->hasFile('proof'))$data['proof_path']=$request->file('proof')->store('dependent-proofs','public');
        unset($data['proof']);$data['verification_status']='pending_verification';$dependent=$account->dependents()->create($data);
        ActivityLog::create(['user_id'=>$request->user()->id,'action'=>'Dependent added','description'=>'Dependent #'.$dependent->id.' submitted for verification.']);
        return redirect()->back()->with('success','Dependent submitted for verification.');
    }
    public function verify(Request $request,PatientDependent $dependent){$data=$request->validate(['verification_status'=>'required|in:verified,rejected,inactive','reason'=>'nullable|required_if:verification_status,rejected|string|max:1000']);$dependent->update(['verification_status'=>$data['verification_status'],'verified_by'=>$request->user()->id,'verified_at'=>now()]);ActivityLog::create(['user_id'=>$request->user()->id,'action'=>'Dependent '.$data['verification_status'],'description'=>'Dependent #'.$dependent->id]);return redirect()->back()->with('success','Dependent status updated.');}
}
