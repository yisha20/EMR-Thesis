<?php
namespace App\Http\Controllers;
use App\CommonComplaintOption;
use Illuminate\Http\Request;
class CommonComplaintOptionController extends Controller{
 public function index(){return view('admin.complaint-options.index',['options'=>CommonComplaintOption::orderBy('category')->orderBy('display_order')->get()]);}
 public function store(Request $r){$d=$r->validate(['name'=>'required|string|max:100|unique:common_complaint_options,name','category'=>'required|string|max:100','description'=>'nullable|string|max:1000','display_order'=>'integer|min:0','allows_multiple'=>'nullable|boolean','requires_details'=>'nullable|boolean']);$d+=['is_active'=>true];$d['allows_multiple']=$r->boolean('allows_multiple');$d['requires_details']=$r->boolean('requires_details');CommonComplaintOption::create($d);return back()->with('success','Complaint option added.');}
 public function update(Request $r,CommonComplaintOption $option){$d=$r->validate(['name'=>'required|string|max:100|unique:common_complaint_options,name,'.$option->id,'category'=>'required|string|max:100','display_order'=>'integer|min:0','is_active'=>'nullable|boolean','allows_multiple'=>'nullable|boolean','requires_details'=>'nullable|boolean']);foreach(['is_active','allows_multiple','requires_details'] as $f)$d[$f]=$r->boolean($f);$option->update($d);return back()->with('success','Complaint option updated.');}
}
