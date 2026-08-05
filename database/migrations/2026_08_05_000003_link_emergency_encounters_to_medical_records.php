<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class LinkEmergencyEncountersToMedicalRecords extends Migration {
 public function up(){Schema::table('medical_records',function(Blueprint $table){$table->unsignedBigInteger('emergency_encounter_id')->nullable()->unique()->after('consultation_id');$table->foreign('emergency_encounter_id')->references('id')->on('emergency_encounters')->onDelete('set null');});}
 public function down(){Schema::table('medical_records',function(Blueprint $table){$table->dropForeign(['emergency_encounter_id']);$table->dropUnique(['emergency_encounter_id']);$table->dropColumn('emergency_encounter_id');});}
}
