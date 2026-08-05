<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddCompletionTimeToEmergencyEncounters extends Migration {
 public function up(){Schema::table('emergency_encounters',function(Blueprint $table){$table->timestamp('completed_at')->nullable()->index()->after('doctor_acknowledged_at');});}
 public function down(){Schema::table('emergency_encounters',function(Blueprint $table){$table->dropIndex(['completed_at']);$table->dropColumn('completed_at');});}
}
