<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class EnforceFacultyDependentPolicy extends Migration {
 public function up(){
  Schema::table('patient_dependents',function(Blueprint $t){$t->boolean('requires_sponsor_review')->default(false)->index();$t->text('review_notes')->nullable();});
  DB::table('patient_dependents as d')->leftJoin('patient_accounts as a','a.id','=','d.sponsor_patient_account_id')->where(function($q){$q->whereNull('a.id')->orWhere('a.patient_type','<>','faculty');})->update(['d.requires_sponsor_review'=>true]);
 }
 public function down(){Schema::table('patient_dependents',function(Blueprint $t){$t->dropIndex(['requires_sponsor_review']);$t->dropColumn(['requires_sponsor_review','review_notes']);});}
}
